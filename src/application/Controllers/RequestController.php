<?php

namespace Soarce\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Soarce\Analyzer\Request as RequestAnalyzer;
use Soarce\Model\SequenceRequest;
use Soarce\Mvc\WebApplicationController;

class RequestController extends WebApplicationController
{
    public function index(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'trace/index.twig');
    }

    public function overview(Request $request, Response $response): Response
    {
        $analyzer = $this->container->get(RequestAnalyzer::class);
        $queryParams = $request->getQueryParams();

        $applicationIds = $queryParams['applicationId'] ?? [];
        $usecaseIds     = $queryParams['usecaseId']     ?? [];

        $viewParams = [
            'applications'   => $analyzer->getAppplications($usecaseIds),
            'applicationIds' => $applicationIds,
            'usecases'       => $analyzer->getUsecases(),
            'usecaseIds'     => $usecaseIds,
            'requests'       => $analyzer->getRequestsOverview($usecaseIds, $applicationIds),
        ];
        return $this->view->render($response, 'request/overview.twig', $viewParams);
    }

    public function request(Request $request, Response $response, $params): Response
    {
        $analyzer = $this->container->get(RequestAnalyzer::class);
        $queryParams = $request->getQueryParams();

        $applicationIds = $queryParams['applicationId'] ?? [];
        $usecaseIds     = $queryParams['usecaseId']     ?? [];
        $requestId      = (int)($params['request'] ?? 0);

        if (0 === $requestId) {
            throw new \InvalidArgumentException('needs a requestId');
        }

        $viewParams = [
            'applicationIds' => $applicationIds,
            'usecaseIds'     => $usecaseIds,
            'request'        => $analyzer->getRequest($requestId),
        ];
        return $this->view->render($response, 'request/request.twig', $viewParams);
    }

    public function sequence(Request $request, Response $response, $params): Response
    {
        $analyzer = $this->container->get(RequestAnalyzer::class);
        $requestId = (int)($params['request'] ?? 0);

        if (0 === $requestId) {
            throw new \InvalidArgumentException('needs a requestId');
        }

        $originalRequest = $analyzer->getRequest($requestId);
        $requests        = $analyzer->getSequence($originalRequest['request_id']);
        $sequence        = SequenceRequest::buildTree($requests);
        $applications    = array_unique(array_column($requests, 'applicationName', 'applicationId'));

        $colPositions = [
            0 => 1,
        ];
        $colPos = 3;
        foreach (array_keys($applications) as $appKey) {
            $colPositions[$appKey] = $colPos;
            $colPos += 2;
        }

        $viewParams = [
            'originalRequest' => $originalRequest,
            'sequence'        => $sequence,
            'applications'    => $applications,
            'colPositions'    => $colPositions,
        ];

        return $this->view->render($response, 'request/sequence.twig', $viewParams);
    }
}
