<?php

namespace Soarce\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Soarce\Maintenance\Database;
use Soarce\Mvc\WebApplicationController;

class MaintenanceController extends WebApplicationController
{
    public function index(Request $request, Response $response): Response
    {
        $databaseMaintenance = $this->container->get(Database::class);

        if ($request->getMethod() === 'POST') {
            $postParams = $request->getParsedBody();

            if ($postParams['action'] === 'reset autoincrement') {
                $databaseMaintenance->resetAutoIncrement();
            } elseif ($postParams['action'] === 'truncate') {
                $databaseMaintenance->purgeAll();
            }
        }

        $viewParams = [
            'DatabaseStatistics' => $this->container->get(\Soarce\Statistics\Database::class)->getMysqlStats(false),
        ];

        return $this->view->render($response, 'maintenance/index.twig', $viewParams);
    }
}
