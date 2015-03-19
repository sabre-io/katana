<?php

namespace Sabre\Katana\Server;

use Sabre\Katana\Configuration;
use Sabre\Katana\Database;
use Sabre\CalDAV;
use Sabre\CardDAV;
use Sabre\DAV;
use Sabre\DAVACL;
use Pimple\Container;


/**
 * Server main class.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Server extends Container {

    /**
     * Path to the configuration file.
     *
     * @const string
     */
    const CONFIGURATION_FILE = 'katana://data/etc/configuration/server.json';

    /**
     * Construct and initialize the server.
     */
    function __construct() {

        $this->initialize();

    }

    /**
     * Initialize the server.
     *
     * @return void
     */
    protected function initialize() {

        $this->initializeConfiguration();
        $this->initializeDatabase();
        $this->initializeBackends();
        $this->initializeTree();
        $this->initializeServer();

    }

    /**
     * Initialize the server configurations.
     *
     * @return void
     */
    protected function initializeConfiguration() {

        $this['configuration'] = new Configuration(static::CONFIGURATION_FILE);

    }

    /**
     * Initialize the database.
     *
     * @return void
     */
    protected function initializeDatabase() {

        $this['database'] = function() {
            $configuration = $this['configuration']->database;
            return new Database(
                $configuration->dsn,
                $configuration->username,
                $configuration->password
            );
        };

    }

    /**
     * Initialize the underlying server.
     *
     * @return void
     */
    protected function initializeServer() {

        $this['server'] = function() {
            $server = new DAV\Server(
                $this['tree']
            );
            $server->setBaseUri(
                $this['configuration']->base_url ?: '/'
            );
            // Browser plugin
            $server->addPlugin(
                new DAV\Browser\Plugin()
            );
            // CalDAV
            $server->addPlugin(new CalDAV\Plugin());
            $server->addPlugin(new CalDAV\Schedule\Plugin());
            // CardDAV
            $server->addPlugin(new CardDAV\Plugin());
            // ACL
            $server->addPlugin(new DAVACL\Plugin());
            return $server;
        };

    }

    /**
     * Initialize all sabredav backends.
     *
     * @return void
     */
    protected function initializeBackends() {

        $this['backend.principals'] = function() {
            return new DAVACL\PrincipalBackend\PDO($this['database']);
        };
        $this['backend.caldav'] = function() {
            return new CalDAV\Backend\PDO($this['database']);
        };
        $this['backend.carddav'] = function() {
            return new CardDAV\Backend\PDO($this['database']);
        };

    }

    /**
     * Initialize the sabredav tree structure
     *
     * @return void
     */
    protected function initializeTree() {

        $this['tree'] = function() {

            return [
                new CalDAV\Principal\Collection($this['backend.principals']),
                new CalDAV\CalendarRoot($this['backend.principals'], $this['backend.caldav']),
                new CardDAV\AddressBookRoot($this['backend.principals'], $this['backend.carddav']),
            ];

        };

    }

    /**
     * Run the server, i.e. consume the current request.
     */
    function run() {

        $this['server']->exec();

    }
}
