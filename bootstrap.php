<?php

/**
 * Bootstrap the project.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */

if (defined('SABRE_KATANA')) {
    return;
}

// Avoid timezone errors. 
date_default_timezone_set('UTC');

use Sabre\Katana;
use Hoa\Core;
use Hoa\File;

/**
 * Load the autoloader.
 */

$autoloadFile =
    __DIR__ . DIRECTORY_SEPARATOR .
   'data' . DIRECTORY_SEPARATOR .
   'lib' . DIRECTORY_SEPARATOR .
   'autoload.php';

if (false === file_exists($autoloadFile)) {

    echo 'Autoloader is not found. Did you run `composer install`?', "\n";
    return;

}

$autoloader = require_once $autoloadFile;

/**
 * Configure hoa:// (basis of katana://).
 */
Core::getInstance()->initialize([
    'root.application'     => __DIR__ . DS,
    'root.data'            => __DIR__ . DS . 'data' . DS,
    'protocol.bin'         => '(:%root.application:)' . 'bin' . DS,
    'protocol.data'        => '(:%root.data:)',
    'protocol.lib'         => '(:%root.application:)' . 'lib' . DS,
    'protocol.public'      => '(:%root.application:)' . 'public' . DS,
    'protocol.tests'       => '(:%root.application:)' . 'tests' . DS,
    'protocol.views'       => '(:%root.application:)' . 'views' . DS
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

/**
 * Current version.
 */
define('SABRE_KATANA_VERSION', '0.1.0');

/**
 * Define the prefix.
 */
define('SABRE_KATANA_PREFIX', __DIR__);

/**
 * Handle exceptions and errors.
 */
Core\Core::enableExceptionHandler(true);
Core\Core::enableErrorHandler(true);

/**
 * Log all exceptions.
 */
event('hoa://Event/Exception')->attach(
    function(Core\Event\Bucket $bucket) {
        $exception = $bucket->getData();
        $filename  = date('Ymd') . '.exceptions.log';
        $file      = new File\Write('katana://data/variable/log/' . $filename);

        $exceptionFile = $exception->getFile();
        $prefixLength  = strlen(SABRE_KATANA_PREFIX);

        if (SABRE_KATANA_PREFIX === substr($exceptionFile, 0, $prefixLength)) {
            $exceptionFile = substr($exceptionFile, $prefixLength + 1);
        }

        $file->writeAll(
            sprintf(
                '[%s] "%s" %s:%d' . "\n",
                date('c'),
                $exception->getMessage(),
                $exceptionFile,
                $exception->getLine()
            )
        );

        $file->close();

        return;
    }
);
