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

namespace Sabre\Katana\Dav\Authentication;

use Sabre\Katana\Database;
use Sabre\DAV\Auth\Backend;
use Sabre\DAV\Server;
use Sabre\DAV\Exception\NotAuthenticated;

/**
 * Basic authentication.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
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

        for ($i = 0; $i < $length; ++$i) {
            $out |= ord($digest[$i]) ^ ord($expectedDigest[$i]);
        }

        return 0 === $out;
    }

    /**
     * Override the parent `authenticate` method to catch the current realm and
     * to remove the WWW-Authenticate header in the response if the
     * X-Requested-With header is present in the request. This last trick
     * prevents the browser to prompt of dialog to the user.
     *
     * @param  Server  $server    Server (not katana, the sabre/dav one).
     * @param  string  $realm     Realm.
     * @return boolean
     * @throw  NotAuthenticated
     */
    public function authenticate(Server $server, $realm)
    {
        $this->_currentRealm = $realm;

        try {

            $out = parent::authenticate($server, $realm);
            $this->_currentRealm = null;

        } catch (NotAuthenticated $exception) {

            $this->_currentRealm = null;
            $request             = $server->httpRequest;
            $response            = $server->httpResponse;

            if ('XMLHttpRequest' === $request->getHeader('X-Requested-With')) {
                $response->removeHeader('WWW-Authenticate');
            }

            throw $exception;

        }

        return $out;
    }
}
