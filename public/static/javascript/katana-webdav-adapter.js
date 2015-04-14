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

Ember.libraries.register('Ember Katana WebDAV Adapter', '0.0.1');

/**
 * …
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
var KatanaWebDAVAdapter = DS.Adapter.extend({

    createRecord: function()
    {
        console.log('KWDAV createRecord');
    },

    updateRecord: function()
    {
        console.log('KWDAV updateRecord');
    },

    deleteRecord: function()
    {
        console.log('KWDAV deleteRecord');
    },

    find: function(store, type, id, snapshot)
    {
        return new Ember.RSVP.Promise(
            function(resolve, reject) {
                resolve({
                    id: id,
                    username: id,
                    displayName: 'Display name of ' + id
                });
            }
        );
    },

    findAll: function(store, type, sinceToken)
    {
        var self      = this;
        var usersURL  = '/server.php/principals/';
        var userRegex = new RegExp('^' + usersURL + '([^/]+)/$');

        return new Ember.RSVP.Promise(
            function(resolve, reject) {
                self.xhr('PROPFIND', usersURL).then(
                    function(data) {
                        var multiStatus = KatanaWebDAVParser.multiStatus(data);
                        var promises    = [];

                        multiStatus.forEach(
                            function(response) {
                                var user = (userRegex.exec(response.href) || [null, null])[1];

                                if (user) {
                                    promises.push(self.find(store, type, user));
                                }

                                return;
                            }
                        );

                        Ember.RSVP.all(promises).then(
                            function(users) {
                                resolve(users);
                                return;
                            }
                        );

                        return;
                    },
                    function(error) {
                        console.log('nok');
                        console.log(error);
                    }
                );
                return;
            }
        );

    },

    findQuery: function()
    {
        console.log('KWDAV findQuery');
    },

    xhr: function(method, url)
    {
        var self = this;
        return new Ember.RSVP.Promise(
            function(resolve, reject) {
                Ember.$.ajax({
                    method     : method,
                    url        : url,
                    processData: false,
                    success    : function(data, status, xhr)
                    {
                        Ember.run(null, resolve, xhr.responseText);
                        return;
                    },
                    error: function(xhr, status, error)
                    {
                        Ember.run(null, reject, error);
                        return;
                    }
                });
            }
        );
    }
});

/**
 * …
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
var KatanaWebDAVParser = {

    namespaces: {
        'DAV:': 'd'
    },

    namespaceResolver: function(alias)
    {
        for (var uri in this.namespaces) {
            if (alias === this.namespaces[uri]) {
                return uri;
            }
        }

        return null;
    },

    xml: function(xml)
    {
        var parser = new DOMParser();
        return parser.parseFromString(xml, 'application/xml');
    },

    getXpathEvaluator: function(xmlDocument)
    {
        var self = this;
        return function(path, node) {
            return xmlDocument.evaluate(
                path,
                node || xmlDocument,
                self.namespaceResolver.bind(self),
                XPathResult.ANY_TYPE,
                null
            );
        };
    },

    multiStatus: function(xml)
    {
        var xmlDocument  = this.xml(xml);
        var xpath        = this.getXpathEvaluator(xmlDocument);
        var result       = [];

        var responses    = xpath('/d:multistatus/d:response');
        var responseNode = responses.iterateNext();

        while (responseNode) {

            var response = {
                href    : xpath('string(d:href)', responseNode).stringValue,
                propStat: []
            };

            var propStats    = xpath('d:propstat', responseNode);
            var propStatNode = propStats.iterateNext();

            while (propStatNode) {

                var propStat = {
                    status    : xpath('string(d:status)', propStatNode).stringValue,
                    properties: {}
                };

                var props    = xpath('d:prop/*', propStatNode);
                var propNode = props.iterateNext();

                while (propNode) {

                    propStat.properties[
                        '{' + propNode.namespaceURI + '}' + propNode.localName
                    ] = propNode.textContent;
                    propNode = props.iterateNext();

                }

                response.propStat.push(propStat);
                propStatNode = propStats.iterateNext();

            }

            result.push(response);
            responseNode = responses.iterateNext();

        }

        return result;
    }

};
