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
use Sabre\Katana\Dav;
use Sabre\Katana\DavAcl;
use Sabre\Katana\CalDav;
use Sabre\Katana\Protocol;
use Sabre\CardDAV as SabreCardDav;
use Sabre\CalDAV as SabreCalDav;
use Sabre\DAV as SabreDav;
use Sabre\DAVACL as SabreDavAcl;

/**
 * Server main class.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Server {

    /**
     * Administrator login.
     *
     * @const string
     */
    const ADMINISTRATOR_LOGIN = 'admin';

    /**
     * Path to the configuration file.
     *
     * @const string
     */
    const CONFIGURATION_FILE  = 'katana://data/configuration/server.json';

    /**
     * sabre/dav server.
     *
     * @var SabreDav\Server
     */
    protected $server        = null;

    /**
     * Server configurations.
     *
     * @var Configuration
     */
    protected $configuration = null;

    /**
     * Database.
     *
     * @var Database
     */
    protected $database      = null;

    /**
     * Construct and initialize the server.
     *
     * @return void
     */
    function __construct() {

        $this->initialize();
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
     *    * CardDAV,
     *    * CalDAV,
     *    * scheduling,
     *    * WebDAV,
     *    * ACL,
     *    * synchronization,
     *    * system.
     *
     * @return void
     */
    protected function initialize() {

        $this->initializeConfiguration();
        $this->initializeDatabase();
        $this->initializeServer();
        $this->initializeAuthentication();
        $this->initializePrincipals($principalBackend);
        $this->initializeCardDAV($principalBackend);
        $this->initializeCalDAV($principalBackend);
        $this->initializeScheduling();
        $this->initializeWebDAV($principalBackend);
        $this->initializeACL();
        $this->initializeSynchronization();
        $this->initializeSystemPlugin();
    }

    /**
     * Initialize the server configurations.
     *
     * @return void
     */
    protected function initializeConfiguration() {

        $this->configuration = new Configuration(static::CONFIGURATION_FILE);
    }

    /**
     * Initialize the database.
     *
     * @return void
     */
    protected function initializeDatabase() {

        $configuration   = $this->getConfiguration()->database;
        $this->database = new Database(
            $configuration->dsn,
            $configuration->username,
            $configuration->password
        );
    }

    /**
     * Initialize the underlying server.
     *
     * @return void
     */
    protected function initializeServer() {

        $this->server = new SabreDav\Server(null);
        $this->server->setBaseUri(
            $this->getConfiguration()->base_url ?: '/'
        );
        $this->server->addPlugin(new SabreDav\Browser\Plugin());
    }

    /**
     * Initialize the authentication.
     *
     * @return void
     */
    protected function initializeAuthentication() {

        $database = $this->getDatabase();
        $backend  = new Dav\Authentication\BasicBackend($database);
        $plugin   = new SabreDav\Auth\Plugin($backend);
        $this->getServer()->addPlugin($plugin);
    }

    /**
     * Initialize the principals.
     *
     * @param  DavAcl\Principal\Backend  &$backend    Retrieve the principals backend by-reference.
     * @return void
     */
    protected function initializePrincipals(DavAcl\Principal\Backend & $backend = null) {

        if (null === $backend) {
            $backend = new DavAcl\Principal\Backend($this->getDatabase());
        }

        $node = new CalDav\Principal\Collection($backend);
        $this->getServer()->tree->getNodeForPath('')->addChild($node);
        $this->getServer()->addPlugin(new DavAcl\User\Plugin($this->getDatabase()));
    }

    /**
     * Initialize CardDAV.
     *
     * @param  DavAcl\Principal\Backend  $principalBackend  The principal backend.
     * @return void
     */
    protected function initializeCardDAV(DavAcl\Principal\Backend $principalBackend) {

        $backend = new SabreCardDav\Backend\PDO($this->getDatabase());
        $node    = new SabreCardDav\AddressBookRoot($principalBackend, $backend);

        $this->getServer()->tree->getNodeForPath('')->addChild($node);
        $this->getServer()->addPlugin(new SabreCardDav\Plugin());
        $this->getServer()->addPlugin(new SabreCardDav\VCFExportPlugin());
    }

    /**
     * Initialize CalDAV.
     *
     * @param  DavACl\Principal\Backend  $principalBackend  The principal backend.
     * @return void
     */
    protected function initializeCalDAV(DavAcl\Principal\Backend $principalBackend) {

        $backend = new SabreCalDav\Backend\PDO($this->getDatabase());
        $node    = new SabreCalDav\CalendarRoot($principalBackend, $backend);
        $this->getServer()->tree->getNodeForPath('')->addChild($node);
        $this->getServer()->addPlugin(new SabreCalDav\Plugin());
        $this->getServer()->addPlugin(new SabreCalDav\ICSExportPlugin());
    }

    /**
     * Initialize scheduling.
     *
     * @return void
     */
    protected function initializeScheduling() {

        $this->getServer()->addPlugin(new SabreCalDav\Schedule\Plugin());
        $configuration = $this->getConfiguration();

        if (isset($configuration->mail)) {
            $this->getServer()->addPlugin(
                new CalDav\Schedule\IMipPlugin($this->getConfiguration())
            );
        }
    }

    /**
     * Initialize WebDAV.
     *
     * @param  DavACl\Principal\Backend  $principalBackend  The principal backend.
     * @return void
     */
    protected function initializeWebDAV(DavAcl\Principal\Backend $principalBackend) {

        $storagePath = Protocol::realPath('katana://data/home');
        $collection  = new DavAcl\File\Home(
            $principalBackend,
            $storagePath,
            'principals'
        );
        $collection->collectionName = 'files';
        $this->getServer()->tree->getNodeForPath('')->addChild($collection);
        $this->getServer()->addPlugin(new DavAcl\File\Plugin($storagePath));

        $database = $this->getDatabase();
        $backend  = new SabreDav\Locks\Backend\PDO($database);
        $plugin   = new SabreDav\Locks\Plugin($backend);
        $this->getServer()->addPlugin($plugin);
    }

    /**
     * Initialize ACL.
     *
     * @return void
     */
    protected function initializeACL() {

        $plugin                               = new SabreDavAcl\Plugin();
        $plugin->adminPrincipals[]            = 'principals/' . static::ADMINISTRATOR_LOGIN;
        $plugin->allowAccessToNodesWithoutACL = true;
        $plugin->hideNodesFromListings        = true;
        $plugin->defaultUsernamePath          = 'principals/';

        $this->getServer()->addPlugin($plugin);
    }

    /**
     * Initialize synchronization.
     *
     * @return void
     */
    protected function initializeSynchronization() {

        $this->getServer()->addPlugin(new SabreDav\Sync\Plugin());
    }

    /**
     * Initialize the plugin system.
     *
     * @return void
     */
    protected function initializeSystemPlugin() {

        $configuration = $this->getConfiguration();
        $this->getServer()->tree->getNodeForPath('')->addChild(
            new Dav\System\Collection([
                new Dav\System\Configuration\Node(),
                new Dav\System\Version\Node()
            ])
        );
        $this->getServer()->addPlugin(new Dav\System\Configuration\Plugin($configuration));
        $this->getServer()->addPlugin(new Dav\System\Version\Plugin());
    }

    /**
     * Get the underlying server.
     *
     * @return SabreDav\Server
     */
    function getServer() {

        return $this->server;
    }

    /**
     * Get the server configurations.
     *
     * @return Configuration
     */
    function getConfiguration() {

        return $this->configuration;
    }

    /**
     * Get the database.
     *
     * @return Database
     */
    function getDatabase() {

        return $this->database;
    }

    /**
     * Run the server, i.e. consume the current request.
     *
     * @return void
     */
    function run() {

        $this->getServer()->exec();
    }
}
