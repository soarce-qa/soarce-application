<?php

use DI\Container;
use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Sentry\ErrorHandler;
use Slim\Views\Twig;
use Soarce\Config;
use Soarce\Twig\TwigExtension;
use Symfony\Component\Dotenv\Dotenv;
use Twig\Extra\Intl\IntlExtension;
use function Sentry\captureException;
use function Sentry\init;

require_once __DIR__ . '/../../vendor/autoload.php';

$builder = new ContainerBuilder();
$builder->addDefinitions([
    'view' => DI\get(Twig::class),
]);

// Make environment variables stored in them accessible via getenv(), $_SERVER or $_ENV
new Dotenv()->load(__DIR__ . '/../../.env');

$container = $builder->build();

$container->set(
    Slim\Views\Twig::class,
    static function(): Twig
    {
        $cache = 'development' === $_ENV['ENV'] ? false : __DIR__ . '/../../temp/cache/twig';
        $view = Twig::create(
            __DIR__ . '/views',
            ['cache' => $cache]
        );

        $environment = $view->getEnvironment();
        TwigExtension::registerFilters($environment);

        $view->addExtension(new IntlExtension());

        return $view;
    }
);

// Initialise Sentry and push it to the DI container.
if (isset($_ENV['SENTRY_DSN']) && $_ENV['SENTRY_DSN'] !== '') {
    $sentryParams = [
        'dsn'               => $_ENV['SENTRY_DSN'],
        'attach_stacktrace' => true,
        'environment'       => $_ENV['SENTRY_ENV'] ?? 'localdev',
    ];

    if (defined('PROJECT_VERSION')) {
        $sentryParams['release'] = PROJECT_VERSION;
    }

    init($sentryParams);

    ErrorHandler::registerOnceErrorHandler();
    ErrorHandler::registerOnceExceptionHandler();
    ErrorHandler::registerOnceFatalErrorHandler();

    $container->set(
        'errorHandler',
        static function (Container $container)
        {
            return static function (Request $request, Response $response, Throwable $exception) use ($container): Response
            {
                captureException($exception);
                return $response;
            };
        }
    );

    $container->set(
        'phpErrorHandler',
        static function (Container $container)
        {
            return static function (Request $request, Response $response, Throwable $error) use ($container): Response
            {
                captureException($error);
                return $response;
            };
        }
    );
}

$container->set(
    mysqli::class,
    static function (): mysqli {
        return mysqli_connect(
            $_ENV['MYSQL_HOST'],
            $_ENV['MYSQL_USER'],
            $_ENV['MYSQL_PASSWORD'],
            $_ENV['MYSQL_DATABASE']
        );
    }
);

$container->set(
    Config::class,
    static function (): Config {
        return new Config(__DIR__ . '/../../soarce.json');
    }
);

$container->set(
    Redis::class,
    static function (Container $container): Redis
    {
        $redis = new Redis();
        $redis->connect($_ENV['REDIS_HOST'], (int)$_ENV['REDIS_PORT']);
        $redis->select($_ENV['REDIS_DB']);
        return $redis;
    }
);

$GLOBALS['container'] = $container;

return $container;
