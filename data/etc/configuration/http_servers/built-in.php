<?php

require_once __DIR__ . '/../../../../bootstrap.php';

use Hoa\Dispatcher;
use Hoa\File;
use Hoa\Mime;
use Hoa\Router;

$router = new Router\Http\Dav();
$router
    ->any(
        'a',
        '.*',
        function(Dispatcher\Kit $_this)
        {
            $uri  = $_this->router->getURI();
            $file = 'katana://Application/Public/' . $uri;

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

            require 'katana://Application/Public/index.php';

            return;
        }
    );

$dispatcher = new Dispatcher\Basic();
$dispatcher->dispatch($router);
