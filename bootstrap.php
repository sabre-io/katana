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
 * Configure hoa:// (basis of katana://).
 */
Core::getInstance()->initialize([
    'root.application'     => __DIR__ . DS,
    'root.data'            => __DIR__ . DS . 'data' . DS,
    'protocol.bin'         => '(:%root.application:)' . 'bin' . DS,
    'protocol.data'        => '(:%root.data:)',
    'protocol.data/lib'    => "\r" . '(:%root.application:)' . 'vendor' . DS,
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
    function (Core\Event\Bucket $bucket) {
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
