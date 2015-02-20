<?php

require_once __DIR__ . '/../bootstrap.php';

use Sabre\Katana\Server\Installer;
use Sabre\Katana\Server\Server;
use Sabre\HTTP;

/**
 * This file is the first to receive the HTTP request and runs the server.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */

/**
* If the applications has not already been installed, redirect to install.php.
 */
if (false === Installer::isInstalled()) {

    $request  = HTTP\Sapi::getRequest();
    $response = new HTTP\Response();

    Installer::redirectToInstall($response, $request);
    HTTP\Sapi::sendResponse($response);

    return;

}

$server = new Server();
$server->run();
