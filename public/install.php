<?php

require_once __DIR__ . '/../bootstrap.php';

use Sabre\Katana\Server\Installer;
use Sabre\Katana\Server\Server;
use Sabre\Katana\Configuration;
use Sabre\HTTP;
use Hoa\Router;
use Hoa\Eventsource;

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
                    Installer::checkBaseUrl($baseUrl)
                );
                return;
            }
        )
        ->get(
            'password',
            '/password/(?<passwords>.*)',
            function($passwords) {
                echo json_encode(
                    Installer::checkPassword($passwords)
                );
                return;
            }
        )
        ->get(
            'install',
            '/install/(?<jsonPayload>.+)',
            function($jsonPayload) {
                $payload = json_decode($jsonPayload);
                $source = new Eventsource\Server();
                $send = function($data) use($source) {
                    $source->step->send(json_encode($data));
                    return;
                };

                $send([
                    'percent' => 5,
                    'message' => 'Create configuration file…'
                ]);
                sleep(1);

                /*
                Installer::createConfigurationFile(
                    Server::CONFIGURATION_FILE
                );
                */

                $send([
                    'percent' => 25,
                    'message' => 'Configuration file created 👍!'
                ]);

                sleep(1);

                $send([
                    'percent' => 30,
                    'message' => 'Create the database…'
                ]);
                sleep(1);

                $send([
                    'percent' => 50,
                    'message' => 'Database created 👍!'
                ]);
                sleep(1);

                $send([
                    'percent' => 55,
                    'message' => 'Create administrator profile…'
                ]);
                sleep(1);

                $send([
                    'percent' => 75,
                    'message' => 'Administrator profile created 👍!'
                ]);
                sleep(1);

                $send([
                    'percent' => 100,
                    'message' => 'sabre/katana is ready!'
                ]);
                sleep(1);
                return;
            }
        );

    $query = substr($url, $pos + 1);
    $router->route($query);

    $dispatcher = new Hoa\Dispatcher\Basic();
    $dispatcher->dispatch($router);

} else {
    echo file_get_contents('katana://application/views/install.html');
}
