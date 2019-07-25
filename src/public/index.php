<?php

use Slim\App;

$container = require '../application/Bootstrap.php';

$app = new App($container);

$app->get('/',         '\Soarce\Application\Controllers\IndexController:index');
$app->any('/receive',  '\Soarce\Application\Controllers\ReceiveController:index');
$app->group('/control', function() {
    $this->get('',                      '\Soarce\Application\Controllers\ControlController:index');
    $this->any('/usecases[/{usecase}]', '\Soarce\Application\Controllers\ControlController:usecase');
});

$app->run();
