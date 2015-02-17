<?php

require __DIR__ . '/../vendor/autoload.php';

use Sabre\Katana\Server\Server;

$server = new Server();
$server->run();
