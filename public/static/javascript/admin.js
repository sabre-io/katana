var ENV = ENV || {};

ENV['katana'] = {
    base_url: '/server.php/'
};
ENV['simple-auth'] = {
    // Declare our custom authorizer.
    authorizer: 'authorizer:custom',

    // The session is stored in memory, it disappears when the application
    // reload.
    store     : 'simple-auth-session-store:ephemeral'
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

        return;
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
        return;
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
                        return;
                    },
                    function(xhr, status, error) {
                        Ember.run(function() {
                            reject();
                        });
                        return;
                    }
                );
                return;
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

        return;
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
            return;
        },

        /**
         * Cancel modal.
         */
        cancelModal: function()
        {
            $('html').removeClass('modal');
            return;
        },

        /**
         * Invalidate the session.
         */
        invalidateSession: function()
        {
            this.get('session').invalidate();
            return;
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

        return;
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

                        return;
                    },
                    function(message) {
                        self.set('valid',      false);
                        self.set('submitting', false);

                        return;
                    }
                );

            return;
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

        Ember.run.scheduleOnce('afterRender', this, function() {

            // Configure the modal behavior.
            $('.ui.modal').modal(
                'setting',
                {
                    transition: 'fade up',
                    closable: false,
                    onDeny: function() {
                        return false;
                    },
                    onApprove: function() {
                        return false;
                    }
                }
            );
        });

        // Check the inactivity of the user.
        var updateLastActivity = function() {
            Katana.lastActivity = new Date();
            return;
        };

        $(window).mousemove(updateLastActivity);
        $(window).click(updateLastActivity);
        $(window).keypress(updateLastActivity);

        return;
    }

});

/**
 * Application adapter.
 */
Katana.ApplicationAdapter = DS.FixtureAdapter;

/**
 * Users route.
 */
Katana.UsersRoute = Ember.Route.extend(SimpleAuth.AuthenticatedRouteMixin, {

    model: function()
    {
        return this.store.find('user');
    }

});

/**
 * User controller.
 */
Katana.UsersController = Ember.ArrayController.extend({

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
            var record = this.store.createRecord(
                'user',
                {
                    displayName: 'Unnamed'
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

            return;
        }

    }

});

/**
 * User route.
 */
Katana.UserRoute = Ember.Route.extend(SimpleAuth.AuthenticatedRouteMixin, {

    model: function(params)
    {
        return this.store.find('user', params.user_id);
    }

});

/**
 * User controller.
 */
Katana.UserController = Ember.ObjectController.extend({

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

        return;
    }.observes('edit'),

    /**
     * Whether the editing mode is active.
     */
    isEditing: false,

    /**
     * Previous username, in case we cancel the current editing.
     */
    previousUsername   : null,

    /**
     * Previous display name, in case we cancel the current editing.
     */
    previousDisplayName: null,

    /**
     * Previous email, in case we cancel the current editing.
     */
    previousEmail      : null,

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
        return;
    }.observes('isEditing'),

    actions: {

        /**
         * Start the editing mode.
         */
        requestEditing: function()
        {
            this.set('previousUsername',    this.get('username'));
            this.set('previousDisplayName', this.get('displayName'));
            this.set('previousEmail',       this.get('email'));
            this.set('isEditing',           true);

            return;
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

            // Newly created record.
            if (true === this.get('isNew')) {

                this.get('model').deleteRecord();
                this.set('isEditing', false);
                this.transitionToRoute('users');

                return;

            }

            this.set('username',    this.get('previousUsername'));
            this.set('displayName', this.get('previousDisplayName'));
            this.set('email',       this.get('previousEmail'));
            this.set('isEditing',   false);

            return;
        },

        /**
         * Save the modification of the user.
         */
        applyEditing: function()
        {
            if (true !== this.get('isEditing')) {
                throw "Cannot save the current user because it was not in the editing mode.";
            }

            this.get('model').save();
            this.set('isEditing', false);
            this.set('edit',      false);

            return;
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
            this.get('model').destroyRecord();
            this.transitionToRoute('users');

            return;
        }

    }

});

/**
 * About route.
 */
Katana.AboutRoute = Ember.Route.extend(SimpleAuth.AuthenticatedRouteMixin);

var attr    = DS.attr;
Katana.User = DS.Model.extend(SimpleValidatorMixin, {
    username: attr('string'),
    displayName: attr('string'),
    email: attr('string'),

    validators: {

        username: function()
        {
            var username = this.get('username');

            if (!username) {
                return {
                    id     : 'username_empty',
                    message: 'Username cannot be empty.'
                };
            }

            return true;
        },

        displayName: function()
        {
            var displayName = this.get('displayName');

            if(!displayName) {
                return {
                    id     : 'displayName_empty',
                    message: 'Display name cannot be empty.'
                }
            }

            return true;
        }

    }
});

Katana.User.FIXTURES = [
    {
        id: 0,
        username: 'gordon',
        displayName: 'Administrator',
        email: 'gordon@freeman.hl'
    },
    {
        id: 1,
        username: 'alix',
        displayName: 'Alix Vence',
        email: 'alix@freeman.hl'
    },
    {
        id: 2,
        username: 'ivan',
        displayName: 'Hywan',
        email: 'ivan@fruux.com'
    }
];
