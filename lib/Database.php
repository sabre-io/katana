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

namespace Sabre\Katana;

use Hoa\File\Finder;
use PDO;

/**
 * This class represents the connection to the database.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class Database extends PDO {

    /**
     * Overload the parent constructor.
     * The username and password might come from the configuration file. Thus,
     * if not set, their values are an empty string. PDO needs no value instead
     * of an empty string. We fix that.
     *
     * @param  string  $dsn         DSN.
     * @param  string  $username    Username.
     * @param  string  $password    Password.
     * @return void
     */
    function __construct($dsn, $username = null, $password = null) {
        if ('sqlite:katana://' === substr($dsn, 0, 16)) {
            $dsn = 'sqlite:' . Protocol::realPath(substr($dsn, 7), false);
        }

        if (empty($username)) {
            parent::__construct($dsn);
        } elseif (empty($password)) {
            parent::__construct($dsn, $username);
        } else {
            parent::__construct($dsn, $username, $password);
        }
    }

    /**
     * Get an iterator over all the template schemas for the active database.
     *
     * @return Finder
     */
    function getTemplateSchemaIterator() {
        $driverName = $this->getAttribute($this::ATTR_DRIVER_NAME);
        $finder     = new Finder();
        $finder
            ->in('katana://data/variable/database/templates/')
            ->name('/\.' . $driverName . '\.sql$/');

        return $finder;
    }
}
