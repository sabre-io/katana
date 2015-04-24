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

namespace Sabre\Katana\Test\Helper;

use Sabre\Katana\Database;

define('HELPER_MYSQL_HOST',     'localhost');
define('HELPER_MYSQL_PORT',     3306);
define('HELPER_MYSQL_USERNAME', 'root');
define('HELPER_MYSQL_PASSWORD', '');
define('HELPER_MYSQL_DSN',      sprintf('mysql:host=%s;port=%d', HELPER_MYSQL_HOST, HELPER_MYSQL_PORT));

/**
 * Helper to get a fresh MySQL database.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class MySQL
{
    /**
     * All created databases.
     *
     * @var array
     */
    protected $databases = [];

    /**
     * Run the helper.
     *
     * @return string
     */
    public function __invoke()
    {
        $database = new Database(
            HELPER_MYSQL_DSN,
            HELPER_MYSQL_USERNAME,
            HELPER_MYSQL_PASSWORD
        );

        $query =
            'SELECT SCHEMA_NAME ' .
            'FROM INFORMATION_SCHEMA.SCHEMATA';

        $databases = iterator_to_array(
            $database->query($query, $database::FETCH_COLUMN, 0)
        );

        while (
            in_array(
                $databaseName = 'sabre_katana_test_' . uniqid(),
                $databases
            )
        );

        $query =
            'CREATE DATABASE ' . $databaseName . ' ' .
            'DEFAULT CHARACTER SET=utf8mb4 ' .
            'DEFAULT COLLATE=utf8mb4_unicode_ci';

        $database->exec($query);
        unset($database);

        $this->databases[] = $databaseName;

        return $databaseName;
    }

    /**
     * Remove created databases.
     *
     * @return void
     */
    public function __destruct()
    {
        if (empty($this->databases)) {
            return;
        }

        $database = new Database(
            HELPER_MYSQL_DSN,
            HELPER_MYSQL_USERNAME,
            HELPER_MYSQL_PASSWORD
        );

        foreach ($this->databases as $databaseName) {
            $database->exec('DROP DATABASE ' . $databaseName);
        }

        return;
    }
}
