<?php

require __DIR__ . '/../vendor/autoload.php';

use Sabre\Katana\Server\Server;

/**
 * This file is the first to receive the HTTP request and runs the server.
 *
 * @copyright Copyright (C) 2007-2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */

date_default_timezone_set('UTC');

$server = new Server();
$server->run();
