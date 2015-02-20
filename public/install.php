<?php

require_once __DIR__ . '/../bootstrap.php';

use Sabre\Katana\Server\Installer;
use Sabre\Katana\Server\Server;
use Sabre\Katana\Configuration;
use Sabre\HTTP;

/**
 * This file aims at installing the application.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */

$response = new HTTP\Response();

/**
 * If the applications has already been installed, redirect to index.php.
 */
if (true === Installer::isInstalled()) {

    $configuration = new Configuration(Server::CONFIGURATION_FILE);

    Installer::redirectToIndex($response, $configuration);
    HTTP\Sapi::sendResponse($response);

    return;

}

var_dump('go installing!');
