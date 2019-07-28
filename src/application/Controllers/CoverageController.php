<?php

namespace Soarce\Application\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Container;
use Soarce\Analyzer\Coverage;

class CoverageController
{
    /** @var Container */
    protected $ci;

    /**
     * BaseController constructor.
     *
     * @param Container $dependencyInjectionContainer
     */
    public function __construct(Container $dependencyInjectionContainer)
    {
        $this->ci = $dependencyInjectionContainer;
    }

    /**
	 * @param  Request $request
	 * @param  Response $response
	 * @return Response
	 */
  	public function index(Request $request, Response $response): Response
    {
        $analyzer = new Coverage($this->ci);

        $viewParams = [
            'files'    => $analyzer->getFiles(),
            'services' => $this->ci->settings['soarce']['services'],
        ];
        return $this->ci->view->render($response, 'coverage/index.twig', $viewParams);
	}

}
