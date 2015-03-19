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
        login           : '',
        password        : '',
        passwordBis     : '',
        email           : '',
        emailBis        : '',
        databaseDriver  : 'sqlite',
        databaseHost    : '',
        databasePort    : 3306,
        databaseName    : '',
        databaseUsername: '',
        databasePassword: '',
        submitting      : false,

        showMySQLPanel  : false,

        onDatabaseDriver: function()
        {
            this.set('showMySQLPanel', 'mysql' === this.get('databaseDriver'));
            this.validate();

            return;
        }.observes('databaseDriver'),

        validate: function()
        {
            var verdict = true;

            verdict = verdict && (false === this.get('invalidBaseUrl'));
            verdict = verdict && (false === this.get('invalidLogin'));
            verdict = verdict && (false === this.get('invalidPassword'));
            verdict = verdict && (false === this.get('invalidEmail'));

            if ('mysql' === this.get('databaseDriver')) {
                verdict = verdict && (false === this.get('invalidDatabase'));
                verdict = verdict && (this.get('databaseHost'));
                verdict = verdict && (this.get('databasePort'));
                verdict = verdict && (this.get('databaseName'));
                verdict = verdict && (this.get('databaseUsername'));
            }

            this.set('valid', verdict);

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
            var login = encodeURIComponent(this.get('login'));
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
            var password    = encodeURIComponent(this.get('password'));
            var passwordBis = encodeURIComponent(this.get('passwordBis'));
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
            var email    = encodeURIComponent(this.get('email'));
            var emailBis = encodeURIComponent(this.get('emailBis'));
            $
                .getJSON('?/email/' + email + emailBis)
                .done(function(verdict) {

                    self.set('invalidEmail', false === verdict);
                    self.validate();

                    return;

                });

            return;
        }.observes('email', 'emailBis'),

        validateDatabase: function()
        {
            var self = this;
            $
                .getJSON(
                    '?/database/' +
                    encodeURIComponent(
                        JSON.stringify({
                            driver  : this.get('databaseDriver'),
                            host    : this.get('databaseHost'),
                            port    : this.get('databasePort'),
                            name    : this.get('databaseName'),
                            username: this.get('databaseUsername'),
                            password: this.get('databasePassword')
                        })
                    )
                )
                .done(function(verdict) {

                    self.set('invalidDatabase', false === verdict);
                    self.validate();

                });
        }.observes(
            'databaseHost',
            'databasePort',
            'databaseName',
            'databaseUsername',
            'databasePassword'
        ),

        actions: {
            submit: function()
            {
                this.set('submitting', true);

                var databaseDriver = this.get('databaseDriver');
                var isMySQL        = 'mysql' === databaseDriver;

                var source = new EventSource(
                    '?/install/' +
                    encodeURIComponent(
                        JSON.stringify({
                            baseurl : this.get('baseUrl'),
                            login   : this.get('login'),
                            email   : this.get('email'),
                            password: this.get('password'),
                            database: {
                                driver  : databaseDriver,
                                host    : isMySQL ? this.get('databaseHost')     : '',
                                port    : isMySQL ? this.get('databasePort')     : '',
                                name    : isMySQL ? this.get('databaseName')     : '',
                                username: isMySQL ? this.get('databaseUsername') : '',
                                password: isMySQL ? this.get('databasePassword') : ''
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
                                3000
                            );
                        }
                    }
                );
            }
        }
    });

    Katana.ApplicationView = Ember.View.extend({

        didInsertElement: function()
        {
            this._super();

            var controller = this.get('controller');

            Ember.run.scheduleOnce('afterRender', this, function() {
                $('.ui.radio.checkbox').checkbox();
                $('#progress').progress();
            });

            $('[name="database_driver"]').on(
                'change',
                function(evt) {
                    controller.set('databaseDriver', evt.target.value);
                }
            );

            return;
        }

    });
})(window);
