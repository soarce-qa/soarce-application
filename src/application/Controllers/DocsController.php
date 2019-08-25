<?php

namespace Soarce\Application\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Container;
use Soarce\Config;
use Soarce\Statistics\Database;

class DocsController
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
        $this->ci->view['activeMainMenu'] = 'docs';
    }

    /**
     * @param  Request  $request
     * @param  Response $response
     * @param  array    $params
     * @return Response
     */
    public function index(Request $request, Response $response, $params): Response
    {
        $page = $params['page'] ?? 'index';
        $this->ci->view['activeSubMenu'] = $page;
        return $this->ci->view->render($response, 'docs/' . $page . '.twig');
    }

    /**
     * @param  Request  $request
     * @param  Response $response
     * @return Response
     */
    public function license(Request $request, Response $response): Response
    {
        $this->ci->view['activeSubMenu'] = 'license';
        return $this->ci->view->render($response, 'docs/license.twig', ['licenseText' => file_get_contents(__DIR__ . '/../../../LICENSE')]);
    }
}
