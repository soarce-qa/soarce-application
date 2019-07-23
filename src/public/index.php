<?php

use Slim\App;

$container = require '../application/Bootstrap.php';

$app = new App($container);

$app->get('/',        '\Soarce\Application\Controllers\IndexController:index');
$app->any('/receive', '\Soarce\Application\Controllers\ReceiveController:index');

$app->run();
