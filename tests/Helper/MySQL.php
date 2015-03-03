<?php

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
 * @license http://sabre.io/license/ Modified BSD License
 */
class MySQL
{
    /**
     * All created databases.
     *
     * @var array
     */
    protected $_databases = [];

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

        $this->_databases[] = $databaseName;

        return $databaseName;
    }

    /**
     * Remove created databases.
     *
     * @return void
     */
    public function __destruct()
    {
        $database = new Database(
            HELPER_MYSQL_DSN,
            HELPER_MYSQL_USERNAME,
            HELPER_MYSQL_PASSWORD
        );

        foreach ($this->_databases as $databaseName) {
            $database->exec('DROP DATABASE ' . $databaseName);
        }

        return;
    }
}
