<?php

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Soarce\Application\Controllers\ControlController;
use Soarce\Application\Controllers\CoverageController;
use Soarce\Application\Controllers\DocsController;
use Soarce\Application\Controllers\IndexController;
use Soarce\Application\Controllers\MaintenanceController;
use Soarce\Application\Controllers\ReceiveController;
use Soarce\Application\Controllers\RequestController;
use Soarce\Application\Controllers\TraceController;

require_once __DIR__ . '/../../vendor/autoload.php';

session_start();
try {
    /** @var Container $container */
    $container = require __DIR__ . '/../application/Bootstrap.php';
    AppFactory::setContainer($container);

    $app = AppFactory::create();

    $app->get('/',            IndexController::class . ':index');
    $app->any('/receive',     ReceiveController::class . ':index');
    $app->any('/maintenance', MaintenanceController::class . ':index');
    $app->group('/control',   function(RouteCollectorProxy $group) {
        $group->get('',                      ControlController::class . ':index');
        $group->any('/usecases[/{usecase}]', ControlController::class . ':usecase');
        $group->any('/services[/{service}]', ControlController::class . ':service');
    });
    $app->group('/coverage',   function(RouteCollectorProxy $group) {
        $group->get('',                                       CoverageController::class . ':index');
        $group->get('/file/{file:[0-9]+}',                    CoverageController::class . ':file');
        $group->get('/file/{file:[0-9]+}/line/{line:[0-9]+}', CoverageController::class . ':line');
    });
    $app->group('/trace',      function(RouteCollectorProxy $group) {
        $group->get('',                          TraceController::class . ':index');
        $group->get('/calls',                    TraceController::class . ':calls');
        $group->get('/calls/{direction:[a-z]+}', TraceController::class . ':callerCallee');
        $group->get('/usecase',                  TraceController::class . ':usecase');
    });
    $app->group('/request',    function(RouteCollectorProxy $group) {
        $group->get('',                           RequestController::class . ':index');
        $group->get('/{request:[0-9]+}',          RequestController::class . ':request');
        $group->get('/overview',                  RequestController::class . ':overview');
        $group->get('/sequence/{request:[0-9]+}', RequestController::class . ':sequence');
    });
    $app->get('/docs/license',  DocsController::class . ':license');
    $app->get('/docs[/{page}]', DocsController::class . ':index');

    // debug, list all routes
    /*
    echo '<pre>';
    foreach ($app->getRouteCollector()->getRoutes() as $key => $value) {
        echo "$key: {$value->getPattern()}\n";
    }
    echo '</pre>';
    */

    // execute

    ob_start();
    $app->run();
} catch (Throwable $e) {
    echo "An error occurred, please investigate, fix and maybe contribute that? Pliiiis?\n\n<pre>";
    echo "MESSAGE: ", $e->getMessage(), "\n";
    echo "CODE:    ", $e->getCode(), "\n";
    echo "FILE:    ", $e->getFile(), "\n";
    echo "LINE:    ", $e->getLine(), "\n";
    echo "TRACE:   ", $e->getTraceAsString(), "\n";
    echo "</pre>\n";
    Sentry\captureException($e);
    die();
}

