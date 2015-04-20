var ENV = ENV || {};

ENV['katana'] = {
    base_url: window.location.pathname.replace(/\/+[^\/]*$/, '/') + 'server.php'
};
ENV['simple-auth'] = {
    // Declare our custom authorizer.
    authorizer         : 'authorizer:custom',

    // The session is stored in memory, it disappears when the application
    // reload.
    store              : 'simple-auth-session-store:ephemeral',

    // Login page.
    authenticationRoute: '/'
};

/**
 * Declare our own custom authenticator and authorizer.
 */
Ember.Application.initializer({
    name      : 'authentication',
    before    : 'simple-auth',
    initialize: function(container, application) {
        container.register('authenticator:custom', Katana.CustomAuthenticator);
        container.register('authorizer:custom',    Katana.CustomAuthorizer);
    }
});

/**
 * Create the application.
 */
Katana = Ember.Application.create({

    /**
     * The last user activity, used to detect inactivity and invalidate the
     * session.
     */
    lastActivity: null,

    ready: function()
    {
        this.lastActivity = new Date();
    }

});

/**
 * The custom authenticator is based on the HTTP Basic Authorization.
 */
Katana.CustomAuthenticator = SimpleAuth.Authenticators.Base.extend({

    /**
     * We never restore the session for security reasons.
     */
    restore: function(data)
    {
        return new Ember.RSVP.Promise(
            function(resolve, reject) {
                reject();
            }
        );
    },

    /**
     * To authenticate the session, we `PROPFIND` the root of the server with
     * the given credentials. If it succeeds, it means the server validates
     * these credentials.
     */
    authenticate: function(credentials)
    {
        var basic = btoa(
            (credentials.username || '') +
            ':' +
            (credentials.password || '')
        );

        var url = ENV.katana.base_url;

        return new Ember.RSVP.Promise(
            function(resolve, reject) {
                Ember.$.ajax({
                    url    : url,
                    type   : 'PROPFIND',
                    headers: {
                        'Authorization': 'Basic ' + basic
                    }
                }).then(
                    function(response, status) {
                        Ember.run(function() {
                            resolve({token: basic});
                        });
                    },
                    function(xhr, status, error) {
                        Ember.run(function() {
                            reject();
                        });
                    }
                );
            }
        );
    },

    /**
     * Invalidating the session is always a success.
     */
    invalidate: function(data)
    {
        return new Ember.RSVP.Promise(
            function(resolve, reject) {
                resolve();
            }
        );
    }

});

/**
 * The custom authorizer is based on the HTTP Basic Authorization.
 */
Katana.CustomAuthorizer = SimpleAuth.Authorizers.Base.extend({

    /**
     * Automatically inject the Basic token for each request.
     */
    authorize: function(xhr, requestOptions)
    {
        var session = this.get('session');

        if (session.content.token) {
            xhr.setRequestHeader(
                'Authorization', 'Basic ' + session.content.token
            );
        }
    }

});

/**
 * Set the root URL of the application.
 */
Katana.Router.reopen({
    rootURL: window.location.pathname
});

/**
 * Declare the router.
 */
Katana.Router.map(function() {
    this.resource('users', function() {
        this.resource('user', {path: ':user_id'});
    });
    this.resource('about');
});

/**
 * Application route.
 */
Katana.ApplicationRoute = Ember.Route.extend(SimpleAuth.ApplicationRouteMixin, {

    actions: {

        /**
         * Request modal for the whole application. No dialog, just focus on an
         * actual part of the application.
         */
        requestModal: function()
        {
            $('html').addClass('modal');
        },

        /**
         * Cancel modal.
         */
        cancelModal: function()
        {
            $('html').removeClass('modal');
        },

        /**
         * Invalidate the session.
         */
        invalidateSession: function()
        {
            this.get('session').invalidate();
        },

        /**
         * Show the alert modal window.
         */
        alert: function(title, content)
        {
            var controller = this.controllerFor('application');
            var oldTitle   = controller.get('alert.title');
            var oldContent = controller.get('alert.content');

            controller.set('alert.title',   title);
            controller.set('alert.content', content);

            var clean = function() {
                controller.set('alert.title',   oldTitle);
                controller.set('alert.content', oldContent);

                return true;
            };

            $('#modalAlert')
                .modal(
                    'setting',
                    {
                        onDeny:    clean,
                        onApprove: clean
                    }
                )
                .modal('show');
        }

    }

});

/**
 * Application controller.
 */
Katana.ApplicationController = Ember.Controller.extend(SimpleAuth.AuthenticationControllerMixin, {

    /**
     * Whether the login form is valid or not.
     */
    valid            : true,

    /**
     * Whether the login form is submitting or not.
     */
    submitting       : false,

    /**
     * Tick for the session activity. Does not contain any useful information.
     * Only allows to react to an event (tick update).
     */
    lastSessionTick  : null,

    /**
     * Number of seconds before the session expires.
     */
    sessionExpireIn  : 0,

    /**
     * Whether the session is about to expire or not.
     */
    sessionWillExpire: false,

    /**
     * Current alert title and message.
     */
    alert: {
        title  : 'Alert',
        content: '(unknown)'
    },

    /**
     * Run a session tick.
     * Either the session is not authenticated and it does not tick. Else it
     * ticks every second. We set the inactivity time to 10mn. 1mn before the
     * end, the session is considered as “about to expire”.
     */
    sessionTick: function()
    {
        var self            = this;
        var elapsedSeconds  = Math.round((new Date() - Katana.lastActivity) / 1000);
        var sessionExpireIn = (10 * 60) - elapsedSeconds;
        var session         = this.get('session');

        this.set('sessionExpireIn', sessionExpireIn);

        if (false === session.isAuthenticated) {
            return;
        }

        if (sessionExpireIn <= 60) {
            this.set('sessionWillExpire', true);
        } else {
            this.set('sessionWillExpire', false);
        }

        if (sessionExpireIn <= 0) {
            this.get('session').invalidate().then(
                null,
                function() {
                    // if invalidating the session failed, we refresh the
                    // application.
                    window.location.reload();
                }
            );
            return;
        }

        Ember.run.later(
            function() {
                self.set('lastSessionTick', new Date());
            },
            1000
        );
    }.observes('lastSessionTick').on('init'),

    actions: {

        /**
         * Call our custom authenticator. If it succeeds, the application starts
         * to tick, else we ask the user to retry.
         */
        authenticate: function()
        {
            var self = this;
            this.set('submitting', true);

            var credentials = {
                username: this.get('username'),
                password: this.get('password')
            };

            this.get('session')
                .authenticate('authenticator:custom', credentials)
                .then(
                    function(message) {
                        self.set('valid',      true);
                        self.set('submitting', false);

                        Ember.run.later(
                            function() {
                                self.set('lastSessionTick', new Date());
                            },
                            1000
                        );

                        $('html').addClass('logged');
                    },
                    function(message) {
                        self.set('valid',      false);
                        self.set('submitting', false);
                    }
                );
        }

    }

});

/**
 * Application view.
 */
Katana.ApplicationView = Ember.View.extend({

    didInsertElement: function()
    {
        this._super();

        Ember.run.scheduleOnce(
            'afterRender',
            this,
            function() {
                // Configure the modal behavior.
                $('.ui.modal').modal(
                    'setting',
                    {
                        closable  : false,
                        onDeny    : function() {
                            return false;
                        },
                        onApprove: function() {
                            return false;
                        }
                    }
                );
            }
        );

        // Check the inactivity of the user.
        var updateLastActivity = function() {
            Katana.lastActivity = new Date();
        };

        $(window).mousemove(updateLastActivity);
        $(window).click(updateLastActivity);
        $(window).keypress(updateLastActivity);
    }

});

/**
 * Application adapter.
 */
Katana.ApplicationAdapter = KatanaWebDAVAdapter;

/**
 * Users route.
 */
Katana.UsersRoute = Ember.Route.extend(SimpleAuth.AuthenticatedRouteMixin, {

    model: function()
    {
        return this.get('store').filter(
            'user',
            {},
            function(user) {
                return true;
            }
        );
    }

});

/**
 * User controller.
 */
Katana.UsersController = Ember.Controller.extend({

    /**
     * Users are sorted by the display name.
     */
    sortProperties: ['displayName'],

    actions: {

        /**
         * Create a new user and start the editing mode.
         */
        requestCreating: function()
        {
            var record = this.get('store').createRecord(
                'user',
                {
                    username   : '',
                    displayName: 'Unnamed',
                    email      : '',
                    newPassword: null
                }
            );
            this.transitionToRoute(
                'user',
                record.get('id'),
                {
                    queryParams: {
                        edit: 'true'
                    }
                }
            );
        }

    }

});

/**
 * User route.
 */
Katana.UserRoute = Ember.Route.extend(SimpleAuth.AuthenticatedRouteMixin, {

    model: function(params)
    {
        return this.get('store').find('user', params.user_id);
    }

});

/**
 * User controller.
 */
Katana.UserController = Ember.Controller.extend({

    queryParams: ['edit'],

    /**
     * Whether the editing mode is asked or not.
     */
    edit: 'false',

    /**
     * If the editing mode is asked, this is an auto-editing.
     */
    autoEditing: function()
    {
        if ('true' === this.get('edit')) {
            this.send('requestEditing');
        }
    }.observes('edit'),

    /**
     * Whether the editing mode is active.
     */
    isEditing: false,

    /**
     *  When we are editing, the application should be in the modal mode.
     */
    autoModal: function()
    {
        this.send(
            true === this.get('isEditing')
                ? 'requestModal'
                : 'cancelModal'
        );
    }.observes('isEditing'),

    /**
     * Username is editable only once: When creating the user.
     */
    isUsernameEditable: function()
    {
        return this.get('isEditing') && this.get('model').get('isNew');
    }.property('isEditing', 'model'),

    actions: {

        /**
         * Start the editing mode.
         */
        requestEditing: function()
        {
            this.set('isEditing', true);
        },

        /**
         * Cancel the editing mode. If the editing user is new (i.e. newly
         * created), then we remove it because it is equivalent to cancel the
         * creation of a user.
         */
        cancelEditing: function()
        {
            if (true !== this.get('isEditing')) {
                throw "Cannot cancel a user editing that is not in editing mode.";
            }

            var model = this.get('model');

            // Newly created record.
            if (true === model.get('isNew')) {

                model.destroyRecord();
                this.set('isEditing', false);
                this.set('edit',      false);
                this.transitionToRoute('users');

                return;

            }

            model.rollback();
            this.set('isEditing', false);
            this.set('edit',      false);
        },

        /**
         * Save the modification of the user.
         */
        applyEditing: function()
        {
            var self = this;

            if (true !== this.get('isEditing')) {
                throw "Cannot save the current user because it was not in the editing mode.";
            }

            this.get('model').validate().then(
                function() {
                    var model = self.get('model');

                    self.set('isEditing', false);
                    self.set('edit',      false);

                    model.save().then(
                        function() {
                            model.set('newPassword', null);
                            self.transitionToRoute(
                                'user',
                                model.get('username')
                            );
                        }
                    );
                }
            );
        },

        /**
         * Ask to delete a user.
         */
        requestDeleting: function()
        {
            var self = this;

            $('#modalUserAskDeleting')
                .modal(
                    'setting',
                    {
                        onDeny: function() {
                            return true;
                        },
                        onApprove: function() {
                            self.send('applyDeleting');
                            return true;
                        }
                    }
                )
                .modal('show');
        },

        /**
         * Really delete a user.
         */
        applyDeleting: function()
        {
            var self        = this;
            var model       = this.get('model');
            var username    = model.get('username');
            var displayName = model.get('displayName');

            model
                .destroyRecord()
                .then(
                    function() {
                        self.transitionToRoute('users');
                    },
                    function() {
                        self.send(
                            'alert',
                            'Cannot delete',
                            'An error occured while deleting ' +
                            '<strong>' + displayName + '</strong> ' +
                            '(' + username + '). ' +
                            'Probably because it is forbidden.'
                        );
                        self.get('model').rollback();
                    }
                );
        }

    }

});

/**
 * About route.
 */
Katana.AboutRoute = Ember.Route.extend(SimpleAuth.AuthenticatedRouteMixin);

Katana.User = DS.Model.extend(KatanaValidatorMixin, {

    username   : DS.attr('string'),
    displayName: DS.attr('string'),
    email      : DS.attr('string'),
    newPassword: DS.attr('string'),

    validators: {

        username: function()
        {
            var defer    = Ember.RSVP.defer();
            var username = this.get('username');
            var id       = this.get('id');

            if (!username) {
                defer.reject({
                    id     : 'username_empty',
                    message: 'Username cannot be empty.'
                });
            } else {
                this.get('store').filter(
                    'user',
                    function(user) {
                        return user.get('id')       !== id &&
                               user.get('username') === username;
                    }
                ).then(function(sameUsers) {
                    if (0 === sameUsers.get('length')) {
                        defer.resolve(username);
                    } else {
                        defer.reject({
                            id     : 'username_unique',
                            message: 'Username must be unique.'
                        });
                    }
                });
            }

            return defer.promise;
        },

        displayName: function()
        {
            var defer       = Ember.RSVP.defer();
            var displayName = this.get('displayName');

            if (!displayName) {
                defer.reject({
                    id     : 'displayName_empty',
                    message: 'Display name cannot be empty.'
                });
            } else {
                defer.resolve(displayName);
            }

            return defer.promise;
        },

        email: function()
        {
            var defer = Ember.RSVP.defer();
            var email = this.get('email');

            if (!/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(email)) {
                defer.reject({
                    id     : 'email_invalid',
                    message: 'Email is not syntactically valid.'
                });
            } else {
                defer.resolve(email);
            }

            return defer.promise;
        },

        newPassword: function()
        {
            var defer       = Ember.RSVP.defer();
            var newPassword = this.get('email');

            if (true === this.get('isNew')) {
                if (!newPassword) {
                    defer.reject({
                        id     : 'newPassword_empty',
                        message: 'New password cannot be empty.'
                    });
                } else {
                    defer.resolve(newPassword);
                }
            } else {
                if (!newPassword) {
                    defer.resolve(null);
                } else {
                    defer.resolve(newPassword);
                }
            }

            return defer.promise;
        }

    }

});
