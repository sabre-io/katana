'use strict';

/**
 * @license
 *
 * sabre/katana.
 * Copyright (C) 2015 fruux GmbH (https://fruux.com/)
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

Ember.libraries.register('Ember Simple Validator', '0.0.1');

/**
 * A simple validator mixin.
 *
 * The class that extends this mixin should declare a `validators` property,
 * pretty similar to the `actions` property. This property is an object of
 * functions representing validators.
 *
 * A validator can return `true` if it valids a datum, `{id: errorId, message:
 * "…"}` if it invalids a datum.
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
 *     {{#if validatorErrors.foo}}…
 *
 * and with the following `validators` object:
 *
 *     validators: {
 *         username: function()
 *         {
 *             if (!username) {
 *                 return {id: "foo", "message: "Bar."};
 *             }
 *
 *             return true;
 *         }
 *     }
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
var SimpleValidatorMixin = Ember.Mixin.create({

    ready: function()
    {
        var self = this;

        for (var validatorName in this.validators) {

            if (this.get(validatorName)) {

                var propertyName = validatorName;
                this.addObserver(
                    propertyName,
                    this.validators,
                    new function() {

                        var validator = self.validators[validatorName];
                        var errorIds  = [];

                        return function(sender, key, value, context, rev) {

                            var out = (validator.bind(self))(
                                sender,
                                key,
                                value,
                                context,
                                rev
                            );

                            if (true !== out) {

                                // Is it a known error?
                                if (-1 === errorIds.indexOf(out.id)) {
                                    errorIds.push(out.id);
                                }

                                // Remove previous errors.
                                errorIds.forEach(
                                    function(errorId) {
                                        if (self.validatorErrors[errorId] &&
                                            errorId !== out.id) {
                                            self.set(
                                                'validatorErrors.' + errorId,
                                                null
                                            );
                                        }
                                    }
                                );

                                // Publish the new error.
                                self.set('validatorErrors.' + out.id, out.message)

                            } else {
                                // Clean errors.
                                errorIds.forEach(
                                    function(errorId) {
                                        if (self.validatorErrors[errorId]) {
                                            self.set(
                                                'validatorErrors.' + errorId,
                                                null
                                            );
                                        }
                                    }
                                );
                            }

                            self.validate();

                            return out;

                        }
                    }
                );

            }

        }

        return;
    },

    validatorErrors: {/* errorId: errorMessage */},
    validators: {},
    valid: true,

    validate: function()
    {
        var verdict = true;

        for (var errorId in this.validatorErrors) {
            verdict = verdict && (null === this.validatorErrors[errorId]);
        }

        this.set('valid', verdict);

        return;
    }
});
