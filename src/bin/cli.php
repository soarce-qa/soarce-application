<?php

use DI\Container;
use Sentry\ErrorHandler;
use Soarce\Application\Cli\Application;
use function Sentry\init;

require_once __DIR__ . '/../../vendor/autoload.php';

if (isset($_ENV['SENTRY_DSN']) && $_ENV['SENTRY_DSN'] !== '') {
    $sentryParams = [
        'dsn' => $_ENV['SENTRY_DSN'],
        'attach_stacktrace' => true,
    ];

    if (defined('PROJECT_VERSION')) {
        $sentryParams['release'] = 'soarce/cli@' . PROJECT_VERSION;
    }

    init($sentryParams);

    ErrorHandler::registerOnceErrorHandler();
    ErrorHandler::registerOnceExceptionHandler();
    ErrorHandler::registerOnceFatalErrorHandler();
}

/** @var Container $container */
$container = require __DIR__ . '/../application/Bootstrap.php';

$app = new Application('MORE-CLI', '1.0', $container);

try {
    $app->run();
} catch (Throwable $e) {
    echo $e->getMessage(), "\n", $e->getTraceAsString();
    die();
}
