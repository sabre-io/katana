<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '.autoload.atoum.php';

/**
 * This file is responsible to configure atoum.
 *
 * @copyright Copyright (C) 2007-2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */

$runner->addExtension(new Atoum\PraspelExtension\Manifest());
$runner->addExtension(new mageekguy\atoum\ruler\extension($script));
$runner->addTestsFromDirectory(__DIR__ . '/Unit/');
$runner->addTestsFromDirectory(__DIR__ . '/Integration/');
