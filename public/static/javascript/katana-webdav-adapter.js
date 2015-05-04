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
 * WebDAV adapter.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
var KatanaWebDAV = {

    getPrincipalsURL: function()
    {
        return ENV.katana.base_url + '/principals/';
    },

    getCalendarsURL: function()
    {
        return ENV.katana.base_url + '/calendars/';
    },

    getAddressBooksURL: function()
    {
        return ENV.katana.base_url + '/addressbooks/';
    },

    xhr: function(method, url, headers, body)
    {
        return new Ember.RSVP.Promise(
            function(resolve, reject) {
                Ember.$.ajax({
                    method     : method,
                    url        : url,
                    data       : body,
                    headers    : headers,
                    processData: false,
                    success    : function(data, status, xhr) {
                        xhr.then = null;
                        Ember.run(null, resolve, xhr.responseText);
                    },
                    error: function(xhr, status, error) {
                        var isObject = xhr !== null && typeof xhr === 'object';

                        if (isObject) {
                            xhr.then = null;

                            if (!xhr.errorThrown) {
                                if (typeof error === 'string') {
                                    xhr.errorThrown = new Error(error);
                                } else {
                                    xhr.errorThrown = error;
                                }
                            }
                        }

                        Ember.run(null, reject, xhr);
                    }
                });
            }
        );
    }

};

var KatanaWebDAVPrincipalsAdapter = DS.Adapter.extend({

    createRecord: function(store, type, snapshot)
    {
        return new Ember.RSVP.Promise(
            function(resolve, reject) {
                // Principals.
                KatanaWebDAV.xhr(
                    'MKCOL',
                    KatanaWebDAV.getPrincipalsURL() + snapshot.get('username'),
                    {
                        'Content-Type': 'application/xml; charset=utf-8'
                    },
                    '<?xml version="1.0" encoding="utf-8" ?>' + "\n" +
                    '<d:mkcol xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns">' + "\n" +
                    '  <d:set>' + "\n" +
                    '    <d:prop>' + "\n" +
                    '      <d:resourcetype>' + "\n" +
                    '        <d:principal />' + "\n" +
                    '      </d:resourcetype>' + "\n" +
                    '      <d:displayname>' + snapshot.get('displayName') + '</d:displayname>' + "\n" +
                    '      <s:email-address>' + snapshot.get('email') + '</s:email-address>' + "\n" +
                    '      <s:password>' + snapshot.get('newPassword') + '</s:password>' + "\n" +
                    '    </d:prop>' + "\n" +
                    '  </d:set>' + "\n" +
                    '</d:mkcol>'
                ).then(
                    function(data) {
                        // One default calendar.
                        return KatanaWebDAV.xhr(
                            'MKCOL',
                            KatanaWebDAV.getCalendarsURL() + snapshot.get('username') + '/' + uuid.v4() + '/',
                            {
                                'Content-Type': 'application/xml; charset=utf-8'
                            },
                            '<?xml version="1.0"?>' + "\n" +
                            '<d:mkcol xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav">' + "\n" +
                            '  <d:set>' + "\n" +
                            '    <d:prop>' + "\n" +
                            '      <d:resourcetype>' + "\n" +
                            '        <d:collection />' + "\n" +
                            '        <c:calendar />' + "\n" +
                            '      </d:resourcetype>' + "\n" +
                            '      <d:displayname>Home</d:displayname>' + "\n" +
                            '      <c:supported-calendar-component-set>' + "\n" +
                            '        <c:comp name="VEVENT" />' + "\n" +
                            '      </c:supported-calendar-component-set>' + "\n" +
                            '      <x:calendar-color xmlns:x="http://apple.com/ns/ical/">#00508CFF</x:calendar-color>' + "\n" +
                            '    </d:prop>' + "\n" +
                            '  </d:set>' + "\n" +
                            '</d:mkcol>'
                        );
                    }
                ).then(
                    function(data) {
                        // Another default calendar.
                        return KatanaWebDAV.xhr(
                            'MKCOL',
                            KatanaWebDAV.getCalendarsURL() + snapshot.get('username') + '/' + uuid.v4() + '/',
                            {
                                'Content-Type': 'application/xml; charset=utf-8'
                            },
                            '<?xml version="1.0"?>' + "\n" +
                            '<d:mkcol xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav">' + "\n" +
                            '  <d:set>' + "\n" +
                            '    <d:prop>' + "\n" +
                            '      <d:resourcetype>' + "\n" +
                            '        <d:collection />' + "\n" +
                            '        <c:calendar />' + "\n" +
                            '      </d:resourcetype>' + "\n" +
                            '      <d:displayname>Work</d:displayname>' + "\n" +
                            '      <c:supported-calendar-component-set>' + "\n" +
                            '        <c:comp name="VEVENT" />' + "\n" +
                            '      </c:supported-calendar-component-set>' + "\n" +
                            '      <x:calendar-color xmlns:x="http://apple.com/ns/ical/">#AF1917FF</x:calendar-color>' + "\n" +
                            '    </d:prop>' + "\n" +
                            '  </d:set>' + "\n" +
                            '</d:mkcol>'
                        );
                    }
                ).then(
                    function(data) {
                        // Default task list.
                        return KatanaWebDAV.xhr(
                            'MKCOL',
                            KatanaWebDAV.getCalendarsURL() + snapshot.get('username') + '/' + uuid.v4() + '/',
                            {
                                'Content-Type': 'application/xml; charset=utf-8'
                            },
                            '<?xml version="1.0"?>' + "\n" +
                            '<d:mkcol xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav">' + "\n" +
                            '  <d:set>' + "\n" +
                            '    <d:prop>' + "\n" +
                            '      <d:resourcetype>' + "\n" +
                            '        <d:collection />' + "\n" +
                            '        <c:calendar />' + "\n" +
                            '      </d:resourcetype>' + "\n" +
                            '      <d:displayname>Tasks</d:displayname>' + "\n" +
                            '      <c:supported-calendar-component-set>' + "\n" +
                            '        <c:comp name="VTODO" />' + "\n" +
                            '      </c:supported-calendar-component-set>' + "\n" +
                            '      <x:calendar-color xmlns:x="http://apple.com/ns/ical/">#AF1917FF</x:calendar-color>' + "\n" +
                            '    </d:prop>' + "\n" +
                            '  </d:set>' + "\n" +
                            '</d:mkcol>'
                        );
                    }
                ).then(
                    function(data) {
                        // Default address book.
                        return KatanaWebDAV.xhr(
                            'MKCOL',
                            KatanaWebDAV.getAddressBooksURL() + snapshot.get('username') + '/' + uuid.v4() + '/',
                            {
                                'Content-Type': 'application/xml; charset=utf-8'
                            },
                            '<?xml version="1.0"?>' + "\n" +
                            '<d:mkcol xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:carddav">' + "\n" +
                            '  <d:set>' + "\n" +
                            '    <d:prop>' + "\n" +
                            '      <d:resourcetype>' + "\n" +
                            '        <d:collection />' + "\n" +
                            '        <c:addressbook />' + "\n" +
                            '      </d:resourcetype>' + "\n" +
                            '      <d:displayname>Contacts</d:displayname>' + "\n" +
                            '    </d:prop>' + "\n" +
                            '  </d:set>' + "\n" +
                            '</d:mkcol>'
                        );
                    }
                ).then(
                    function(data) {
                        resolve({
                            id: snapshot.get('username')
                        });
                    },
                    function(xhr) {
                        console.log('nok');
                        console.log(xhr);
                        reject(xhr);
                    }
                );
            }
        );
    },

    updateRecord: function(store, type, snapshot)
    {
        return new Ember.RSVP.Promise(
            function(resolve, reject) {
                KatanaWebDAV.xhr(
                    'PROPPATCH',
                    KatanaWebDAV.getPrincipalsURL() + snapshot.get('username'),
                    {
                        'Content-Type': 'application/xml; charset=utf-8'
                    },
                    '<?xml version="1.0" encoding="utf-8" ?>' + "\n" +
                    '<d:propertyupdate xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns">' + "\n" +
                    '  <d:set>' + "\n" +
                    '    <d:prop>' + "\n" +
                    '      <d:displayname>' + snapshot.get('displayName') + '</d:displayname>' + "\n" +
                    '      <s:email-address>' + snapshot.get('email') + '</s:email-address>' + "\n" +
                    (snapshot.get('newPassword') ?
                    '      <s:password>' + snapshot.get('newPassword') + '</s:password>' + "\n" :
                    '') +
                    '    </d:prop>' + "\n" +
                    '  </d:set>' + "\n" +
                    '</d:propertyupdate>'
                ).then(
                    function(data) {
                        resolve(data);
                    },
                    function(xhr) {
                        console.log('nok');
                        console.log(xhr);
                        reject(xhr);
                    }
                );
            }
        );
    },

    deleteRecord: function(store, type, snapshot)
    {
        return new Ember.RSVP.Promise(
            function(resolve, reject) {
                KatanaWebDAV.xhr(
                    'DELETE',
                    KatanaWebDAV.getPrincipalsURL() + snapshot.get('username')
                ).then(
                    function(data) {
                        resolve(data);
                    },
                    function(xhr) {
                        console.log('nok');
                        console.log(xhr);
                        reject(xhr);
                    }
                );
            }
        );
    },

    find: function(store, type, id, snapshot)
    {
        return new Ember.RSVP.Promise(
            function(resolve, reject) {
                KatanaWebDAV.xhr(
                    'PROPFIND',
                    KatanaWebDAV.getPrincipalsURL() + id,
                    {
                        'Content-Type': 'application/xml; charset=utf-8'
                    },
                    '<?xml version="1.0" encoding="utf-8" ?>' + "\n" +
                    '<d:propfind xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns">' + "\n" +
                    '  <d:prop>' + "\n" +
                    '    <d:displayname />' + "\n" +
                    '    <s:email-address />' + "\n" +
                    '  </d:prop>' + "\n" +
                    '</d:propfind>'
                ).then(
                    function(data) {
                        var multiStatus = KatanaWebDAVParser.multiStatus(data);
                        var properties  = multiStatus[0].propStat[0].prop;

                        resolve({
                            id         : id,
                            username   : id,
                            displayName: properties['{DAV:}displayname'],
                            email      : properties['{http://sabredav.org/ns}email-address'],
                            newPassword: null
                        });
                    },
                    function(xhr) {
                        console.log('nok');
                        console.log(xhr);
                        reject(xhr);
                    }
                );
            }
        );
    },

    findAll: function(store, type, sinceToken)
    {
        var userRegex = new RegExp('^' + KatanaWebDAV.getPrincipalsURL() + '([^/]+)/$');

        return new Ember.RSVP.Promise(
            function(resolve, reject) {
                KatanaWebDAV.xhr(
                    'PROPFIND',
                    KatanaWebDAV.getPrincipalsURL(),
                    {
                        'Content-Type': 'application/xml; charset=utf-8'
                    },
                    '<?xml version="1.0" encoding="utf-8" ?>' + "\n" +
                    '<d:propfind xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns">' + "\n" +
                    '  <d:prop>' + "\n" +
                    '    <d:displayname />' + "\n" +
                    '    <s:email-address />' + "\n" +
                    '  </d:prop>' + "\n" +
                    '</d:propfind>'
                ).then(
                    function(data) {
                        var multiStatus = KatanaWebDAVParser.multiStatus(data);
                        var users       = [];

                        multiStatus.forEach(
                            function(response) {
                                var user = (userRegex.exec(response.href) || [null, null])[1];

                                if (user) {
                                    var properties  = response.propStat[0].prop;
                                    users.push({
                                        id         : user,
                                        username   : user,
                                        displayName: properties['{DAV:}displayname'],
                                        email      : properties['{http://sabredav.org/ns}email-address'],
                                        newPassword: null
                                    });
                                }
                            }
                        );

                        resolve(users);
                    },
                    function(xhr) {
                        console.log('nok');
                        console.log(xhr);
                        reject(xhr);
                    }
                );
            }
        );
    },

    findQuery: function(store, type, query, recordArray)
    {
        return this.findAll(store, type, null);
    },

    generateIdForRecord: function(store, inputProperties)
    {
        return 'new';
    }

});

var KatanaCalDAVAdapter = DS.Adapter.extend({

    createRecord: function(store, type, snapshot)
    {
        console.log('CalDAV adapter createRecord');
    },

    updateRecord: function(store, type, snapshot)
    {
        console.log('CalDAV adapter updateRecord');
    },

    deleteRecord: function(store, type, snapshot)
    {
        console.log('CalDAV adapter deleteRecord');
    },

    find: function(store, type, id, snapshot)
    {
        console.log('CalDAV adapter find');
    },

    findAll: function(store, type, sinceToken)
    {
        console.log('CalDAV adapter findAll');
        window._ = store;
        console.log(store);
        console.log(type);
    },

    findQuery: function(store, type, query, recordArray)
    {
        var username = query.username;
        var type     = (query.type || 'vevent').toUpperCase();

        if (undefined === username) {
            return null;
        }

        var calendarRegex = new RegExp('^' + KatanaWebDAV.getCalendarsURL() + username + '/([^/]+)/$');

        return new Ember.RSVP.Promise(
            function(resolve, reject) {
                KatanaWebDAV.xhr(
                    'PROPFIND',
                    KatanaWebDAV.getCalendarsURL() + username,
                    {
                        'Content-Type': 'application/xml; charset=utf-8'
                    },
                    '<?xml version="1.0" encoding="utf-8" ?>' + "\n" +
                    '<d:propfind xmlns:d="DAV:" xmlns:cal="urn:ietf:params:xml:ns:caldav" xmlns:apple="http://apple.com/ns/ical/">' + "\n" +
                    '  <d:prop>' + "\n" +
                    '    <d:displayname />' + "\n" +
                    '    <cal:supported-calendar-component-set />' + "\n" +
                    '    <apple:calendar-color />' + "\n" +
                    '  </d:prop>' + "\n" +
                    '</d:propfind>'
                ).then(
                    function(data) {
                        var multiStatus = KatanaWebDAVParser.multiStatus(data);
                        var calendars   = [];

                        multiStatus.forEach(
                            function(response) {
                                var calendar = (calendarRegex.exec(response.href) || [null, null])[1];

                                if (calendar &&
                                    'HTTP/1.1 200 OK' === response.propStat[0].status) {
                                    var properties = response.propStat[0].prop;

                                    if (type !== properties['{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set'].children[0].attributes['{}name']) {
                                        return;
                                    }

                                    calendars.push({
                                        id          : calendar,
                                        calendarName: calendar,
                                        displayName : properties['{DAV:}displayname'],
                                        color       : properties['{http://apple.com/ns/ical/}calendar-color'],
                                        user        : username
                                    });
                                }
                            }
                        );

                        resolve(calendars);
                    },
                    function(xhr) {
                        console.log('nok');
                        console.log(xhr);
                        reject(xhr);
                    }
                );
            }
        );
    },

    generateIdForRecord: function(store, inputProperties)
    {
        return 'new';
    }

});

/**
 * Some utilities related to XML and WebDAV.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
var KatanaWebDAVParser = {

    namespaces: {
        'DAV:': 'd'
    },

    /**
     * Namespace resolving function.
     */
    namespaceResolver: function(alias)
    {
        for (var uri in this.namespaces) {
            if (alias === this.namespaces[uri]) {
                return uri;
            }
        }

        return null;
    },

    /**
     * Parse an XML string and return an XMLDocument object.
     */
    xml: function(xml)
    {
        var parser = new DOMParser();

        return parser.parseFromString(xml, 'application/xml');
    },

    /**
     * The PrimiteXMLElement object.
     */
    PrimitiveXMLElement: function(name, attributes, children, value) {
        this.name       = name;
        this.attributes = attributes || {};
        this.children   = children || [];
        this.value      = value;

        this.toString   = function() {
            return this.value;
        };
    },

    /**
     * Compile an XML document into Javascript primitive.
     */
    xmlToPrimitive: function(node)
    {
        var value    = null;
        var children = [];
        var child    = null;

        for (var i = 0; i < node.children.length; ++i) {
            children.push(this.xmlToPrimitive(node.children[i]));
        }

        if (0 === children.length) {
            value = node.textContent;
        }

        return new this.PrimitiveXMLElement(
            '{' + node.namespaceURI + '}' + node.localName,
            this.xmlAttributesToPrimitive(node.attributes),
            children,
            value
        );
    },

    /**
     * Compile an XML attributes into Javascript primitives (an object).
     */
    xmlAttributesToPrimitive: function(attributes)
    {
        var out       = {};
        var attribute = null;

        for(var i = 0; i < attributes.length; ++i) {
            attribute = attributes[i];

            out[
                '{' + (attribute.namespaceURI || '') + '}' + attribute.nodeName
            ] = attribute.nodeValue;

            attribute = null;
        }

        return out;
    },

    /**
     * Get a pre-configured XPath evaluator.
     */
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

    /**
     * Parse a multi-status payload.
     */
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
                    status: xpath('string(d:status)', propStatNode).stringValue,
                    prop  : {}
                };

                var props    = xpath('d:prop/*', propStatNode);
                var propNode = props.iterateNext();

                while (propNode) {
                    var handle                 = this.xmlToPrimitive(propNode);
                    propStat.prop[handle.name] = handle;

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
