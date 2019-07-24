<?php

namespace Soarce\Application\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Container;
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
    }

    /**
	 * @param  Request $request
	 * @param  Response $response
	 * @return Response
	 */
	public function index(Request $request, Response $response): Response
    {
        $dbstatistics = new Database($this->ci);
        $this->ci->view['DatabaseStatistics'] = $dbstatistics->getMysqlStats();

		return $this->ci->view->render($response, 'index/index.twig');
	}

}
