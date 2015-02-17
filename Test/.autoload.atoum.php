<?php

/**
 * This file is responsible to configure the autoloader.
 *
 * @copyright Copyright (C) 2007-2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */

$autoLoader = require_once __DIR__ . '/../vendor/autoload.php';
$autoLoader->addPsr4('Sabre\\Katana\\Test\\', __DIR__);
$autoLoader->addPsr4('Mock\\', __DIR__ . '/Mock');
