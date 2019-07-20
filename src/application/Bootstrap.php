<?php

use Slim\App;
use Slim\Views\Twig;

class Bootstrap
{
    /** @var App */
    private $app;

    private function initDependencyInjection(): void
    {
        $container = $this->app->getContainer();
        $view = new Twig(__DIR__ . '/views/', [__DIR__ . '/temp/cache/twig']);

		// Instantiate and add Slim specific extension
		$basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
		$view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));
		$view['serverUrl']     = $container['settings']['serverUrl'];
		$view['baseUrl']       = $container['settings']['baseUrl'];
		$container['view'] = $view;

		$container['database'] = static function ($container) {
		    $mysqli = mysqli_connect(
		        $container->settings['soarce']['database']['host'],
		        $container->settings['soarce']['database']['user'],
		        $container->settings['soarce']['database']['password'],
		        $container->settings['soarce']['database']['database']
            );
		    return $mysqli;
        };

    }

	/**
	 * Define all the routes -- this will be crazy, but well... it's a small project/framework
	 */
    private function initRouting(): void
    {
        $this->app->get('/',        '\Soarce\Application\Controllers\IndexController:index');
        $this->app->any('/receive', '\Soarce\Application\Controllers\ReceiveController:index');
    }

    /**
     * Bootstrap constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function boot()
    {
        $this->initDependencyInjection();
        $this->initRouting();
    }
}

(new Bootstrap($app))->boot();

