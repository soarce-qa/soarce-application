<?php

use Slim\Container;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

require_once __DIR__ . '/../../vendor/autoload.php';

$container = new Container(require __DIR__ . '/../settings.php');

$container['mysqli'] = static function ($container): mysqli {
    $mysqli = mysqli_connect(
        $container->settings['soarce']['database']['host'],
        $container->settings['soarce']['database']['user'],
        $container->settings['soarce']['database']['password'],
        $container->settings['soarce']['database']['database']
    );

    return $mysqli;
};

$container['view'] = static function (Container $container): Twig {
    $view = new Twig(__DIR__ . '/views/', [__DIR__ . '/temp/cache/twig']);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new TwigExtension($container['router'], $basePath));

    return $view;
};

return $container;
