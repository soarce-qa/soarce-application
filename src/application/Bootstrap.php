<?php

use Sentry\ErrorHandler;
use Slim\Container;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Soarce\View\Helper\Bytes;
use Soarce\View\Helper\StripCommonPath;
use Twig\TwigFilter;
use function Sentry\init;

require_once __DIR__ . '/../../vendor/autoload.php';

if (isset($_ENV['SENTRY_DSN']) && $_ENV['SENTRY_DSN'] !== '') {
    init([
        'dsn' => $_ENV['SENTRY_DSN'],
#        'release' => (string)PROJECT_VERSION,
        'attach_stacktrace' => true,
    ]);

    ErrorHandler::registerOnceErrorHandler();
    ErrorHandler::registerOnceExceptionHandler();
    ErrorHandler::registerOnceFatalErrorHandler();
}

$container = new Container(require __DIR__ . '/../settings.php');

$container['mysqli'] = static function ($container): mysqli {
    $mysqli = mysqli_connect(
        $container->settings['database']['host'],
        $container->settings['database']['user'],
        $container->settings['database']['password'],
        $container->settings['database']['database']
    );

    return $mysqli;
};

$container['view'] = static function (Container $container): Twig {
    $view = new Twig(__DIR__ . '/views/', [__DIR__ . '/temp/cache/twig']);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new TwigExtension($container['router'], $basePath));

    $twig = $view->getEnvironment();

    $filter = new TwigFilter('byte', static function ($bytes) {
        return Bytes::filter($bytes);
    });
    $twig->addFilter($filter);

    $filter = new TwigFilter('stripCommonPath', static function ($path, $commonPath) {
        return StripCommonPath::filter($path, $commonPath);
    });
    $twig->addFilter($filter);

    return $view;
};

return $container;
