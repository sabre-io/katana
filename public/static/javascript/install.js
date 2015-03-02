(function(scope) {
    scope.Katana = Ember.Application.create();

    Katana.ApplicationController = Ember.Controller.extend({

        valid          : false,
        invalidBaseUrl : false,
        invalidLogin   : null,
        invalidPassword: null,
        invalidEmail   : null,
        origin         : window.location.origin,
        baseUrl        : function()
        {
            return window.location.pathname.replace(/\/+[^\/]*$/, '/');
        }.property(),
        login          : null,
        password       : null,
        passwordBis    : null,
        email          : null,
        emailBis       : null,
        databaseDriver : function()
        {
            return $('input[type="radio"][name="database_driver"]:checked')[0]
                   .getAttribute('value');
        }.property(),
        submitting     : false,

        validate: function()
        {
            this.set(
                'valid',
                (false === this.get('invalidBaseUrl'))  &&
                (false === this.get('invalidLogin'))    &&
                (false === this.get('invalidPassword')) &&
                (false === this.get('invalidEmail'))
            );
            return;
        },

        validateBaseUrl: function()
        {
            var self = this;
            $
                .getJSON('?/baseurl/' + this.get('baseUrl'))
                .done(function(verdict) {

                    self.set('invalidBaseUrl', false === verdict);
                    self.validate();

                    return;

                });

            return;
        }.observes('baseUrl'),

        validateLogin: function()
        {
            var self  = this;
            var login = encodeURIComponent(this.get('login') || '');
            $
                .getJSON('?/login/' + login)
                .done(function(verdict) {

                    self.set('invalidLogin', false === verdict);
                    self.validate();

                    return;

                });
        }.observes('login'),

        validatePassword: function()
        {
            var self        = this;
            var password    = encodeURIComponent(this.get('password')    || '');
            var passwordBis = encodeURIComponent(this.get('passwordBis') || '');
            $
                .getJSON('?/password/' + password + passwordBis)
                .done(function(verdict) {

                    self.set('invalidPassword', false === verdict);
                    self.validate();

                    return;

                });

            return;
        }.observes('password', 'passwordBis'),

        validateEmail: function()
        {
            var self     = this;
            var email    = encodeURIComponent(this.get('email')    || '');
            var emailBis = encodeURIComponent(this.get('emailBis') || '');
            $
                .getJSON('?/email/' + email + emailBis)
                .done(function(verdict) {

                    self.set('invalidEmail', false === verdict);
                    self.validate();

                    return;

                });

            return;
        }.observes('email', 'emailBis'),

        actions: {
            submit: function()
            {
                this.set('submitting', true);
                var source = new EventSource(
                    '?/install/' +
                    encodeURIComponent(
                        JSON.stringify({
                            baseurl : this.get('baseUrl'),
                            login   : this.get('login'),
                            email   : this.get('email'),
                            password: this.get('password'),
                            database: {
                                driver  : this.get('databaseDriver'),
                                host    : '',
                                port    : '',
                                name    : '',
                                username: '',
                                password: ''
                            }
                        })
                    )
                );
                source.addEventListener(
                    'step',
                    function(evt) {
                        var data = JSON.parse(evt.data);

                        if (-1 === data.percent || 100 === data.percent) {
                            source.close();
                        }

                        if (-1 === data.percent) {
                            $('#progress').addClass('error');
                        } else {
                            $('#progress').progress({
                                percent: data.percent
                            });
                        }

                        $('#progress .label').text(data.message);

                        if (100 === data.percent) {
                            setTimeout(
                                function() {
                                    window.location = '/';
                                },
                                5000
                            );
                        }
                    }
                );
            }
        }
    });

    Katana.ApplicationView = Ember.View.extend({

        didInsertElement: function() {
            this._super();
            Ember.run.scheduleOnce('afterRender', this, function() {
                $('.ui.radio.checkbox').checkbox();
                $('#progress').progress();
            });
        }

    });
})(window);
