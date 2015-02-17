<?php

$autoLoader = require_once __DIR__ . '/../vendor/autoload.php';
$autoLoader->addPsr4('Sabre\\Katana\\Test\\', __DIR__);
$autoLoader->addPsr4('Mock\\', __DIR__ . '/Mock');
