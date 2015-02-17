<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '.autoload.atoum.php';

$runner->addExtension(new Atoum\PraspelExtension\Manifest());
$runner->addTestsFromDirectory(__DIR__ . '/Unit/');
