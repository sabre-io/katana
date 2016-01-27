'use strict';

/**
 * @license
 *
 * sabre/katana.
 * Copyright (C) 2016 fruux GmbH (https://fruux.com/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

Ember.libraries.register('Ember Katana Validator', '0.0.3');

/**
 * A simple validator mixin.
 *
 * The class that extends this mixin should declare a `validators` property,
 * pretty similar to the `actions` property. This property is an object of
 * functions representing validators.
 *
 * A validator returns a deferred promise that resolve or reject a datum, It
 * rejects `{id: errorId, message: "…"}` if it invalids a datum.
 * If the key of this object is a property name of the `Ember.Object`, then an
 * observer is automatically added to auto-validate this property without doing
 * anything. Else, the `validate` method must be call manually.
 *
 * The `valid` property is a boolean, indicating if all the validators validate
 * the data.
 *
 * When a validator invalids a datum, the message is set on the
 * `validatorErrors[errorId]` entry. Thus, in the view, we can have the
 * following code:
 *
 *     {{#if model.validatorErrors.foo}}…
 *
 * and with the following `validators` object:
 *
 *     validators: {
 *         username: function()
 *         {
 *             var defer = Ember.RSVP.defer();
 *
 *             if (!username) {
 *                 defer.reject({id: "foo", "message: "Bar."});
 *             } else {
 *                 defer.resolve(username);
 *             }
 *
 *             return defer.promise;
 *         }
 *     }
 *
 * @copyright Copyright (C) 2016 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
var KatanaValidatorMixin = Ember.Mixin.create({

    init: function()
    {
        this._super.apply(this, arguments);

        if (!(this instanceof DS.Model)) {
            this.ready();
        }
    },

    ready: function()
    {
        var self = this;

        for (var validatorName in this.validators) {
            this._validatorErrorBuckets[validatorName] = [];

            if (undefined === this.get(validatorName)) {
                continue;
            }

            var propertyName = validatorName;
            this.addObserver(
                propertyName,
                this.validators,
                new function() {
                    var validator = self.validators[validatorName];
                    var name      = validatorName;

                    return function(sender, key, value, context, rev) {
                        var promise = (validator.bind(self))(
                            sender,
                            key,
                            value,
                            context,
                            rev
                        );

                        promise.then(
                            self._onResolve.bind(
                                self,
                                self._validatorErrorBuckets[name]
                            ),
                            self._onReject.bind(
                                self,
                                self._validatorErrorBuckets[name]
                            )
                        ).finally(function() {
                            self.set('valid', self._computeVerdict());
                        });
                    }
                }
            );
        }
    },

    /**
     * Each validator has its own error bucket. It associates a message to an
     * ID.
     */
    _validatorErrorBuckets: {},

    /**
     * All validator errors.
     */
    validatorErrors       : {/* errorId : errorMessage */},

    /**
     * User-defined validators.
     */
    validators            : {},

    /**
     * A boolean indicating whether all the data are valid or not.
     */
    valid                 : true,

    /**
     * Clean errors when a property is valid.
     */
    _onResolve: function(errorBucket)
    {
        var self = this;

        // Clean errors.
        errorBucket.forEach(
            function(errorId) {
                if (self.validatorErrors[errorId]) {
                    self.set(
                        'validatorErrors.' + errorId,
                        null
                    );
                }
            }
        );
    },

    /**
     * Deal with errors.
     */
    _onReject: function(errorBucket, error)
    {
        var self = this;

        // Is it a known error?
        if (-1 === errorBucket.indexOf(error.id)) {
            errorBucket.push(error.id);
        }

        // Remove previous errors.
        errorBucket.forEach(
            function(errorId) {
                if (self.validatorErrors[errorId] &&
                    errorId !== error.id) {
                    self.set(
                        'validatorErrors.' + errorId,
                        null
                    );
                }
            }
        );

        // Publish the new error.
        this.set(
            'validatorErrors.' + error.id,
            error.message
        );
    },

    /**
     * Compute the verdict, i.e. are all data valid?
     */
    _computeVerdict: function()
    {
        var verdict = true;

        for (var errorId in this.validatorErrors) {
            verdict = verdict && (null === this.validatorErrors[errorId]);
        }

        if (true === verdict) {
            var promise = this.get('_validatePromise');

            if (promise) {
                promise.resolve(true);
            }
        }

        return verdict;
    },

    /**
     * Clear all errors.
     */
    clearAllErrors: function()
    {
        var buckets = this._validatorErrorBuckets;

        for (var validatorName in buckets) {
            buckets[validatorName] = [];
        }

        for (var errorId in this.validatorErrors) {
            this.set('validatorErrors.' + errorId, null);
        }
    },

    /**
     * Validate all the data.
     */
    validate: function()
    {
        var self              = this;
        var validatorPromises = [];

        this.set('valid', false);

        for (var validatorName in this.validators) {
            if (undefined === this.get(validatorName)) {
                continue;
            }

            validatorPromises.push(
                new Ember.RSVP.Promise(
                    new function() {
                        var name = validatorName;

                        return function(resolve, reject) {
                            var promise = (self.validators[name].bind(self))(
                                null,
                                null,
                                self.get(name),
                                null,
                                null
                            );

                            promise.then(
                                function(data) {
                                    self._onResolve.call(
                                        self,
                                        self._validatorErrorBuckets[name],
                                        data
                                    );
                                    resolve(true);
                                },
                                function(error) {
                                    self._onReject.call(
                                        self,
                                        self._validatorErrorBuckets[name],
                                        error
                                    );
                                    reject(error);
                                }
                            );
                        }
                    }
                )
            );
        }

        var defer = Ember.RSVP.defer();

        Ember.RSVP.allSettled(validatorPromises).then(
            function(data) {
                var fulfilled = [];
                var rejected  = [];

                data.forEach(
                    function(result) {
                        if ('rejected' === result.state) {
                            rejected.push(result.reason);
                        } else {
                            fulfilled.push(result.value);
                        }
                    }
                );

                if (0 === rejected.length) {
                    defer.resolve(fulfilled);
                } else {
                    defer.reject(rejected);
                }
            },
            function(error) {
                defer.reject(error);
            }
        ).finally(function() {
            self.set('valid', self._computeVerdict());
        });

        return defer.promise;
    },

    /**
     * Override the `destroyRecord` method to clear the errors before
     * destroying.
     */
    destroyRecord: function()
    {
        this.clearAllErrors();

        return this._super();
    }
});
