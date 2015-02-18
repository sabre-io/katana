<?php

/**
 * Bootstrap the project.
 *
 * @copyright Copyright (C) 2007-2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */

if (defined('SABRE_KATANA')) {
    return;
}

use Sabre\Katana;
use Hoa\Core;

/**
 * Load the autoloader.
 */

$autoloadFile =
    __DIR__ . DIRECTORY_SEPARATOR .
   'vendor' . DIRECTORY_SEPARATOR .
   'autoload.php';

if (false === file_exists($autoloadFile)) {

    echo 'Autoloader is not found. Did you run `composer install`?', "\n";
    return;

}

require_once $autoloadFile;

/**
 * Configure hoa:// (based of katana://).
 */
Core::getInstance()->initialize([
    'root.application'                => __DIR__ . DS,
    'root.data'                       => __DIR__ . DS . 'data' . DS,
    'protocol.Application/Public'     => 'public' . DS,
    'protocol.Data/Etc'               => 'etc' . DS,
    'protocol.Data/Etc/Configuration' => 'configuration' . DS,
    'protocol.Data/Etc/Locale'        => 'locale' . DS,
    'protocol.Data/Lost+found'        => 'lost+found' . DS,
    'protocol.Data/Variable'          => 'variable' . DS,
    'protocol.Data/Variable/Database' => 'database' . DS,
    'protocol.Data/Variable/Log'      => 'log' . DS,
]);

/**
 * Register the katana:// protocol.
 */
stream_wrapper_register(Katana\Protocol::SCHEME, 'Sabre\Katana\Protocol');

/**
 * Set the default timezone.
 */
date_default_timezone_set('UTC');

/**
 * sabre/katana is now defined and set up, let the world knows that.
 */
define('SABRE_KATANA', true);
