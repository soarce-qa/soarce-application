<?php

namespace Soarce\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Soarce\Analyzer\Coverage;
use Soarce\Mvc\WebApplicationController;
use Soarce\Statistics\Database;

class IndexController extends WebApplicationController
{
     public function index(Request $request, Response $response): Response
    {
        $viewVars = [
#            'configIsValid' => Config::isValid(__DIR__ . '/../../../soarce.json'),
#            'configErrorMessage' => Config::$validationError,
            'DatabaseStatistics' => $this->container->get(Database::class)->getMysqlStats(),
            'totalCoverage'  => $this->container->get(Coverage::class)->getTotalCoveragePercentage(),
        ];

        return $this->view->render($response, 'index/index.twig', $viewVars);
    }
}
