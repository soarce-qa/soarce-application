<?php

namespace Soarce\Application\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Container;
use Soarce\Statistics\Database;

class MaintenanceController
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
        $databaseMaintenance = new \Soarce\Maintenance\Database($this->ci);

        if ($request->isPost()) {
            if ($request->getParam('action') === 'reset autoincrement') {
                $databaseMaintenance->resetAutoIncrement();
            } elseif ($request->getParam('action') === 'truncate') {
                $databaseMaintenance->purgeAll();
            }
        }

		return $this->ci->view->render($response, 'maintenance/index.twig');
	}

}
