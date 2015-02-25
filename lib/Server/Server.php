<?php

namespace Sabre\Katana\Server;

use Sabre\Katana\Configuration;
use Sabre\Katana\Database;
use Sabre\CalDAV;
use Sabre\CardDAV;
use Sabre\DAV;
use Sabre\DAVACL;

/**
 * Server main class.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
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
     *    * principals,
     *    * CalDAV,
     *    * CardDAV.
     *
     * @return void
     */
    protected function initialize()
    {
        $this->initializeConfiguration();
        $this->initializeDatabase();
        $this->initializeServer();
        $this->initializePrincipals($principalBackend);
        $this->initializeCalDAV($principalBackend);
        $this->initializeCardDAV($principalBackend);

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
