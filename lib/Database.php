<?php

namespace Sabre\Katana;

use Sabre\Katana\Protocol;
use Hoa\File\Finder;
use PDO;

/**
 * This class represents the connection to the database.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Database extends PDO
{
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
    public function __construct($dsn, $username = null, $password = null)
    {
        if ('sqlite:katana://' === substr($dsn, 0, 16)) {
            $dsn = 'sqlite:' . Protocol::realPath(substr($dsn, 7), false);
        }

        if (empty($username)) {
            parent::__construct($dsn);
        } elseif(empty($password)) {
            parent::__construct($dsn, $username);
        } else {
            parent::__construct($dsn, $username, $password);
        }

        return;
    }

    /**
     * Get an iterator over all the template schemas for the active database.
     *
     * @return Finder
     */
    public function getTemplateSchemaIterator()
    {
        $driverName = $this->getAttribute($this::ATTR_DRIVER_NAME);
        $finder     = new Finder();
        $finder
            ->in('katana://data/variable/database/templates/')
            ->name('/\.' . $driverName . '\.sql$/');

        return $finder;
    }
}
