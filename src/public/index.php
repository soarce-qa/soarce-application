<?php

use Slim\App;

$container = require '../application/Bootstrap.php';

$app = new App($container);

$app->get('/',            '\Soarce\Application\Controllers\IndexController:index');
$app->any('/receive',     '\Soarce\Application\Controllers\ReceiveController:index');
$app->any('/maintenance', '\Soarce\Application\Controllers\MaintenanceController:index');
$app->group('/control',   function() {
    $this->get('',                      '\Soarce\Application\Controllers\ControlController:index');
    $this->any('/usecases[/{usecase}]', '\Soarce\Application\Controllers\ControlController:usecase');
    $this->any('/services[/{service}]', '\Soarce\Application\Controllers\ControlController:service');
});
$app->group('/coverage',   function() {
    $this->get('',                    '\Soarce\Application\Controllers\CoverageController:index');
});

$app->run();
