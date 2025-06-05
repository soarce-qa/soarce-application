<?php

namespace Soarce\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Soarce\Mvc\WebApplicationController;

class DocsController extends WebApplicationController
{
    public function index(Request $request, Response $response, array $params): Response
    {
        $page = $params['page'] ?? 'index';
        return $this->view->render($response, 'docs/' . $page . '.twig');
    }

    /**
     * @param  Request  $request
     * @param  Response $response
     * @return Response
     */
    public function license(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'docs/license.twig', ['licenseText' => file_get_contents(__DIR__ . '/../../../LICENSE')]);
    }
}
