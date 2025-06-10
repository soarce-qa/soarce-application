<?php

namespace Soarce\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Soarce\Analyzer\Coverage;
use Soarce\Config;
use Soarce\Mvc\WebApplicationController;

class CoverageController extends WebApplicationController
{
    public function index(Request $request, Response $response, array $args): Response
    {
        $analyzer = $this->container->get(Coverage::class);
        $queryParams = $request->getQueryParams();

        $applicationIds = $queryParams['applicationId'] ?? [];
        $usecaseIds     = $queryParams['usecaseId']     ?? [];
        $requestIds     = $queryParams['requestId']     ?? [];

        $viewParams = [
            'applications'   => $analyzer->getAppplications($usecaseIds),
            'applicationIds' => $applicationIds,
            'usecases'       => $analyzer->getUsecases(),
            'usecaseIds'     => $usecaseIds,
            'requests'       => $analyzer->getRequests($usecaseIds, $applicationIds),
            'requestIds'     => $requestIds,
            'files'          => $analyzer->getFiles($applicationIds, $usecaseIds, $requestIds),
            'services'       => $this->container->get(Config::class)->getServices(),
        ];
        return $this->view->render($response, 'coverage/index.twig', $viewParams);
    }

    public function file(Request $request, Response $response, array $params): Response
    {
        $analyzer = $this->container->get(Coverage::class);
        $queryParams = $request->getQueryParams();

        $applicationIds = $queryParams['applicationId'] ?? [];
        $usecaseIds     = $queryParams['usecaseId']     ?? [];
        $requestIds     = $queryParams['requestId']     ?? [];
        $fileId         = (int)($params['file'] ?? 0);

        if (0 === $fileId) {
            throw new \InvalidArgumentException('needs a fileId');
        }

        $viewParams = [
            'fileId'         => $fileId,
            'applicationIds' => $applicationIds,
            'usecaseIds'     => $usecaseIds,
            'requestIds'     => $requestIds,
            'file'           => $analyzer->getFile($fileId),
            'usecases'       => $analyzer->getUsecases($fileId),
            'requests'       => $analyzer->getRequests($usecaseIds, null, $fileId),
            'source'         => $analyzer->getSource($fileId),
            'coverage'       => $analyzer->getCoverage($fileId, $usecaseIds, $requestIds),
        ];
        return $this->view->render($response, 'coverage/file.twig', $viewParams);
    }

    public function line(Request $request, Response $response, array $params): Response
    {
        $analyzer = $this->container->get(Coverage::class);

        $fileId        = (int)($params['file'] ?? 0);
        $lineId        = (int)($params['line'] ?? 0);

        if (0 === $fileId) {
            throw new \InvalidArgumentException('needs a fileId');
        }

        if (0 === $lineId) {
            throw new \InvalidArgumentException('needs a lineId');
        }

        header('Last-Modified:' . gmdate('D, d M Y H:i:s ', time() + 30) . 'GMT');
        header('Expires:' .       gmdate('D, d M Y H:i:s ', time() + 30) . 'GMT');
        header('Content-Type: application/json');
        header('Cache-Control: max-age=30');
        header('Pragma: cache');

        $requests = $analyzer->getRequestsForLoc($fileId, $lineId);
        $usecases = $analyzer->getUsecasesForLoC($fileId, $lineId);

        echo json_encode([
            'usecases' => $usecases,
            'requests' => $requests,
        ], JSON_PRETTY_PRINT);
        die();
    }
}
