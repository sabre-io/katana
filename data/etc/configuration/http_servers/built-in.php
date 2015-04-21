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

require_once __DIR__ . '/../../../../bootstrap.php';

use Hoa\Dispatcher;
use Hoa\File;
use Hoa\Mime;
use Hoa\Router;

/**
 * Router of the HTTP server.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */

$router = new Router\Http\Dav();
$router
    ->any(
        'a',
        '.*',
        function (Dispatcher\Kit $_this) {
            $uri  = $_this->router->getURI();
            $file = 'katana://public/' . rtrim($uri, '/');

            if (!empty($uri) && true === file_exists($file)) {
                if ('.php' === substr($file, -4)) {
                    require $file;

                    return;
                }

                $stream = new File\Read($file);

                try {
                    $mime  = new Mime($stream);
                    $_mime = $mime->getMime();
                } catch (Mime\Exception $e) {
                    $_mime = 'text/plain';
                }

                header('Content-Type: ' . $_mime);
                echo $stream->readAll();

                return;
            }

            require 'katana://public/server.php';

            return;
        }
    );

$dispatcher = new Dispatcher\Basic();
$dispatcher->dispatch($router);
