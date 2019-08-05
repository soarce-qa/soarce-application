<?php

namespace Soarce\Application\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Container;
use Soarce\Config;
use Soarce\Statistics\Database;

class IndexController
{
    /** @var Container */
    protected $ci;

    /**
     * BaseController constructor.
     * @param Container $dependcyInjectionContainer
     */
    public function __construct(Container $dependcyInjectionContainer)
    {
        $this->ci = $dependcyInjectionContainer;
        $this->ci->view['activeMainMenu'] = 'home';
    }

    /**
	 * @param  Request $request
	 * @param  Response $response
	 * @return Response
	 */
	public function index(Request $request, Response $response): Response
    {
        $this->ci->view['configIsValid'] = Config::isValid(__DIR__ . '/../../../soarce.json');
        $this->ci->view['configErrorMessage'] = Config::$validationError;

        $dbstatistics = new Database($this->ci);
        $this->ci->view['DatabaseStatistics'] = $dbstatistics->getMysqlStats();

		return $this->ci->view->render($response, 'index/index.twig');
	}

}
