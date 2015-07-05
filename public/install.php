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
require_once __DIR__ . '/../bootstrap.php';

use Sabre\Katana\Server\Installer;
use Sabre\Katana\Configuration;
use Sabre\HTTP;
use Hoa\Router;
use Hoa\Dispatcher;
use Hoa\Eventsource;
use Hoa\File;

/**
 * This file aims at installing the application.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
$request  = HTTP\Sapi::getRequest();
$response = new HTTP\Response();

/**
 * If the application has already been installed, redirect to the index.
 */
if (true === Installer::isInstalled()) {

    echo file_get_contents(SABRE_KATANA_PREFIX . '/resource/view/install_done.html');

    return;

}

/**
 * If dependencies have not been installed, we print a specific message.
 */
if (true === Installer::isDirectoryEmpty(SABRE_KATANA_PREFIX . '/public/static/vendor/')) {
    echo file_get_contents(SABRE_KATANA_PREFIX . '/resource/view/install_bower.html');

    return;
}

/**
 * If the application has not the correct permissions.
 */
$view = function($directoryName, $directory) {
    $user        = get_current_user();
    $userId      = getmyuid();
    $groupId     = getmygid();
    $permissions = $directory->getReadablePermissions();

    require SABRE_KATANA_PREFIX . '/resource/view/install_permissions.html';
};

$writableDirectories = [
    '/data',
    '/data/home',
    '/data/database',
    '/data/configuration',
    '/data/log',
];

foreach ($writableDirectories as $dir) {

    if (!is_writable(SABRE_KATANA_PREFIX . $dir)) {
        $directoryName = $dir;
        $user          = get_current_user();
        $userId        = getmyuid();
        $groupId       = getmygid();
        require SABRE_KATANA_PREFIX . '/resource/view/install_permissions.html';
        return;
    }

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

    $response->addHeader('Content-Type', 'application/json');

    $router = new Router\Http();
    $router
        ->post(
            'baseurl',
            '/(?-i)baseurl',
            function() use ($request, $response) {
                $response->setBody(
                    json_encode(
                        Installer::checkBaseUrl($request->getBodyAsString())
                    )
                );
                HTTP\Sapi::sendResponse($response);
            }
        )
        ->post(
            'password',
            '/(?-i)password',
            function() use ($request, $response) {
                $response->setBody(
                    json_encode(
                        Installer::checkPassword($request->getBodyAsString())
                    )
                );
                HTTP\Sapi::sendResponse($response);
            }
        )
        ->post(
            'email',
            '/(?-i)email',
            function() use ($request, $response) {
                 $response->setBody(
                    json_encode(
                        Installer::checkEmail($request->getBodyAsString())
                    )
                );
                HTTP\Sapi::sendResponse($response);
            }
        )
        ->post(
            'database',
            '/(?-i)database',
            function() use ($request, $response) {
                $payload = json_decode($request->getBodyAsString(), true);
                $out     = false;

                if (is_array($payload)) {
                    try {
                        $out = Installer::checkDatabase($payload);
                    } catch (\Exception $exception) {
                        $out = [
                            'error' => $exception->getMessage()
                        ];
                    }
                }

                $response->setBody(json_encode($out));
                HTTP\Sapi::sendResponse($response);
            }
        )
        ->get(
            'install',
            '/(?-i)install/(?<jsonpayload>.+)',
            function($jsonpayload) {
                $payload = json_decode($jsonpayload);
                $source  = new Eventsource\Server();
                $send    = function($data) use ($source) {
                    $source->step->send(json_encode($data));
                    sleep(1);
                };

                try {

                    $send([
                        'percent' => 5,
                        'message' => 'Create configuration file…'
                    ]);

                    $configuration = Installer::createConfigurationFile(
                        SABRE_KATANA_CONFIG,
                        [
                            'baseUrl'  => $payload->baseurl,
                            'database' => [
                                'driver'   => $payload->database->driver,
                                'host'     => $payload->database->host,
                                'port'     => $payload->database->port,
                                'name'     => $payload->database->name,
                                'username' => $payload->database->username,
                                'password' => $payload->database->password
                            ]
                        ]
                    );

                    $send([
                        'percent' => 25,
                        'message' => 'Configuration file created 👍!'
                    ]);
                    $send([
                        'percent' => 30,
                        'message' => 'Create the database…'
                    ]);

                    $database = Installer::createDatabase($configuration);

                    $send([
                        'percent' => 50,
                        'message' => 'Database created 👍!'
                    ]);

                    $send([
                        'percent' => 55,
                        'message' => 'Create administrator profile…'
                    ]);

                    Installer::createAdministratorProfile(
                        $configuration,
                        $database,
                        $payload->email,
                        $payload->password
                    );

                    $send([
                        'percent' => 75,
                        'message' => 'Administrator profile created 👍!'
                    ]);
                    $send([
                        'percent' => 100,
                        'message' => 'sabre/katana is ready!'
                    ]);

                } catch (\Exception $e) {
                    $send([
                        'percent' => -1,
                        'message' => 'An error occured: ' . $e->getMessage()
                    ]);
                    // + log
                }
            }
        );

    $query = substr($url, $pos + 1);

    if (false !== $posEnd = strpos($query, '&')) {
        $query = substr($query, 0, $posEnd);
    }

    $router->route($query, '/');

    $dispatcher = new Dispatcher\Basic();
    $dispatcher->dispatch($router);

} else {
    echo file_get_contents(SABRE_KATANA_PREFIX . '/resource/view/install.html');
}
