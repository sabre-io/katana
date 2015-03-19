<?php

namespace Sabre\Katana\Dav\Authentification;

use Sabre\Katana\Database;
use Sabre\DAV\Auth\Backend;
use Sabre\DAV\Server;

/**
 * Basic authentification.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class BasicBackend extends Backend\AbstractBasic
{
    /**
     * Database.
     *
     * @var Database
     */
    protected $_database   = null;

    /**
     * Current realm.
     * Must be null outside `authenticate` calls.
     *
     * @var string
     */
    private $_currentRealm = null;

    /**
     * Constructor.
     *
     * @param  Database  $database    Database.
     * @return void
     */
    public function __construct(Database $database)
    {
        $this->_database = $database;

        return;
    }

    /**
     * Validate the username and password.
     * We use a timing attack resistant approach.
     *
     * @param  string  $username    Username.
     * @param  string  $password    Password.
     * @return boolean
     */
    protected function validateUserPass($username, $password)
    {
        $database  = $this->_database;
        $statement = $database->prepare(
            'SELECT digesta1 FROM users WHERE username = :username'
        );
        $statement->execute(['username' => $username]);

        $digest         = $statement->fetch($database::FETCH_COLUMN, 0);
        $expectedDigest = md5(
            $username . ':' .
            $this->_currentRealm . ':' .
            $password
        );

        $length = mb_strlen($digest, '8bit');

        if ($length !== mb_strlen($expectedDigest, '8bit')) {
            return false;
        }

        $out = 0;

        for($i = 0; $i < $length; ++$i) {
            $out |= ord($digest[$i]) ^ ord($expectedDigest[$i]);
        }

        return 0 === $out;
    }

    /**
     * Override the parent `authenticate` method to catch the current realm.
     *
     * @param  Server  $server    Server (not katana, the sabre/dav one).
     * @param  string  $realm     Realm.
     * @return boolean
     * @throw  Sabre\DAV\Exception\NotAuthenticated
     */
    public function authenticate(Server $server, $realm)
    {
        $this->_currentRealm = $realm;
        $out = parent::authenticate($server, $realm);
        $this->_currentRealm = null;

        return $out;
    }
}
