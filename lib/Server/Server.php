<?php

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

namespace Sabre\Katana\Server;

use Sabre\Katana\Configuration;
use Sabre\Katana\Database;
use Sabre\Katana\Dav\Authentication;
use Sabre\CalDAV;
use Sabre\CardDAV;
use Sabre\DAV;
use Sabre\DAVACL;

/**
 * Server main class.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Server
{
    /**
     * Path to the configuration file.
     *
     * @const string
     */
    const CONFIGURATION_FILE = 'katana://data/etc/configuration/server.json';

    /**
     * sabre/dav server.
     *
     * @var DAV\Server
     */
    protected $_server        = null;

    /**
     * Server configurations.
     *
     * @var Configuration
     */
    protected $_configuration = null;

    /**
     * Database.
     *
     * @var Database
     */
    protected $_database      = null;

    /**
     * Construct and initialize the server.
     *
     * @return void
     */
    public function __construct()
    {
        $this->initialize();

        return;
    }

    /**
     * Initialize the server.
     *
     * In this order:
     *    * configurations,
     *    * database,
     *    * server,
     *    * authentication,
     *    * principals,
     *    * CalDAV,
     *    * CardDAV,
     *    * ACL,
     *    * synchronization.
     *
     * @return void
     */
    protected function initialize()
    {
        $this->initializeConfiguration();
        $this->initializeDatabase();
        $this->initializeServer();
        $this->initializeAuthentication();
        $this->initializePrincipals($principalBackend);
        $this->initializeCalDAV($principalBackend);
        $this->initializeCardDAV($principalBackend);
        $this->initializeACL();
        $this->initializeSynchronization();

        return;
    }

    /**
     * Initialize the server configurations.
     *
     * @return void
     */
    protected function initializeConfiguration()
    {
        $this->_configuration = new Configuration(static::CONFIGURATION_FILE);

        return;
    }

    /**
     * Initialize the database.
     *
     * @return void
     */
    protected function initializeDatabase()
    {
        $configuration   = $this->getConfiguration()->database;
        $this->_database = new Database(
            $configuration->dsn,
            $configuration->username,
            $configuration->password
        );

        return;
    }

    /**
     * Initialize the underlying server.
     *
     * @return void
     */
    protected function initializeServer()
    {
        $this->_server = new DAV\Server(null);
        $this->_server->setBaseUri(
            $this->getConfiguration()->base_url ?: '/'
        );
        $this->_server->addPlugin(new DAV\Browser\Plugin());

        return;
    }

    /**
     * Initialize the authentication.
     *
     * @return void
     */
    protected function initializeAuthentication()
    {
        $configuration = $this->getConfiguration()->authentication;
        $database      = $this->getDatabase();
        $backend       = new Authentication\BasicBackend($database);
        $plugin        = new DAV\Auth\Plugin($backend, $configuration->realm);
        $this->getServer()->addPlugin($plugin);

        return;
    }

    /**
     * Initialize the principals.
     *
     * @param  DAVACL\PrincipalBackend\PDO  &$backend    Retrieve the principals backend by-reference.
     * @return void
     */
    protected function initializePrincipals(DAVACL\PrincipalBackend\PDO &$backend = null)
    {
        if (null === $backend) {
            $backend = new DAVACL\PrincipalBackend\PDO($this->getDatabase());
        }

        $node = new CalDAV\Principal\Collection($backend);
        $this->getServer()->tree->getNodeForPath('')->addChild($node);

        return;
    }

    /**
     * Initialize CalDAV.
     *
     * @param  DAVACL\PrincipalBackend\PDO  $principalBackend  The principal backend.
     * @return void
     */
    protected function initializeCalDAV(DAVACL\PrincipalBackend\PDO $principalBackend)
    {
        $backend = new CalDAV\Backend\PDO($this->getDatabase());
        $node    = new CalDAV\CalendarRoot($principalBackend, $backend);
        $this->getServer()->tree->getNodeForPath('')->addChild($node);
        $this->getServer()->addPlugin(new CalDAV\Plugin());
        $this->getServer()->addPlugin(new CalDAV\Schedule\Plugin());

        return;
    }

    /**
     * Initialize CardDAV.
     *
     * @param  DAVACL\PrincipalBackend\PDO  $principalBackend  The principal backend.
     * @return void
     */
    protected function initializeCardDAV(DAVACL\PrincipalBackend\PDO $principalBackend)
    {
        $backend = new CardDAV\Backend\PDO($this->getDatabase());
        $node    = new CardDAV\AddressBookRoot($principalBackend, $backend);
        $this->getServer()->tree->getNodeForPath('')->addChild($node);
        $this->getServer()->addPlugin(new CardDAV\Plugin());

        return;
    }

    /**
     * Initialize ACL.
     *
     * @return void
     */
    protected function initializeACL()
    {
        $this->getServer()->addPlugin(new DAVACL\Plugin());

        return;
    }

    /**
     * Initialize synchronization.
     *
     * @return void
     */
    protected function initializeSynchronization()
    {
        $this->getServer()->addPlugin(new DAV\Sync\Plugin());

        return;
    }

    /**
     * Get the underlying server.
     *
     * @return DAV\Server
     */
    public function getServer()
    {
        return $this->_server;
    }

    /**
     * Get the server configurations.
     *
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->_configuration;
    }

    /**
     * Get the database.
     *
     * @return Database
     */
    public function getDatabase()
    {
        return $this->_database;
    }

    /**
     * Run the server, i.e. consume the current request.
     */
    public function run()
    {
        $this->getServer()->exec();

        return;
    }
}
