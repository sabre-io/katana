<?php

require_once __DIR__ . '/../bootstrap.php';

use Sabre\Katana\Server\Installer;
use Sabre\Katana\Server\Server;
use Sabre\Katana\Configuration;
use Sabre\HTTP;
use Hoa\Router;

/**
 * This file aims at installing the application.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */

$request  = HTTP\Sapi::getRequest();
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

$url   = $request->getUrl();
$query = '';

/**
 * We compute asynchronous tasks from the installation page.
 * They have the following form: install?/<command>
 *
 * Else, we print the installation page.
 */
if (false !== $pos = strpos($url, '?')) {

    $router = new Hoa\Router\Http();
    $router
        ->get(
            'baseurl',
            '/baseurl/(?<baseUrl>.*)',
            function($baseUrl) {
                echo json_encode(
                    0 !== preg_match('#/$#', $baseUrl)
                );

                return;
            }
        )
        ->get(
            'password',
            '/password/(?<passwords>.*)',
            function($passwords) {
                $length = strlen($passwords);

                if (0 === $length || 0 !== ($length % 2)) {

                    echo json_encode(false);
                    return;

                }

                echo json_encode(
                    substr($passwords, 0, $length / 2)
                    ===
                    substr($passwords, $length / 2)
                );
            }
        );

    $query = substr($url, $pos + 1);
    $router->route($query);

    $dispatcher = new Hoa\Dispatcher\Basic();
    $dispatcher->dispatch($router);

} else {
    echo file_get_contents('katana://application/views/install.html');
}
