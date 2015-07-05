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
use Sabre\Katana;
use Hoa\Core;
use Hoa\File;

/**
 * Bootstrap the project.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
if (defined('SABRE_KATANA')) {
    return;
}

/**
 * Set the default timezone.
 */
date_default_timezone_set('UTC');

/**
 * Load the autoloader.
 */
$autoloadFile =
    __DIR__ . DIRECTORY_SEPARATOR .
   'vendor' . DIRECTORY_SEPARATOR .
   'autoload.php';

if (false === file_exists($autoloadFile)) {
    echo 'Autoloader is not found. Did you run `composer install`?', "\n";
    exit(1);
}

$autoloader = require_once $autoloadFile;

/**
 * sabre/katana is now defined and set up, let the world knows that.
 */
define('SABRE_KATANA', true);

/**
 * Define the prefix.
 */
define('SABRE_KATANA_PREFIX', __DIR__);


/**
 * Default path to configuration file
 */
define('SABRE_KATANA_CONFIG', SABRE_KATANA_PREFIX . '/data/configuration/server.json');

//Mapping PHP errors to exceptions
function exception_error_handler($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("exception_error_handler");
