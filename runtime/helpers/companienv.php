<?php
<<<CONFIG
packages:
    - "sroze/companienv: ^0.0.7"
CONFIG;

use Companienv\Application;

if (!class_exists('Companienv\Application')) {
    require __DIR__ . '/vendor/autoload.php';
}

$rootDirectory = dirname(dirname(__DIR__));
$application = new Application($rootDirectory);
$application->run();
