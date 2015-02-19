<?php

require_once __DIR__ . '/../bootstrap.php';

use Sabre\Katana\Server\Server;

/**
 * This file is the first to receive the HTTP request and runs the server.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */

$server = new Server();
$server->run();
