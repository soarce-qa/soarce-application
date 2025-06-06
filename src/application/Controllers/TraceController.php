<?php

namespace Soarce\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Soarce\Analyzer\Trace;
use Soarce\Mvc\WebApplicationController;

class TraceController extends WebApplicationController
{
    public function index(Request $request, Response $response, array $args): Response
    {
        return $this->view->render($response, 'trace/index.twig');
    }

    public function calls(Request $request, Response $response, array $args): Response
    {
        $analyzer = $this->container->get(Trace::class);
        $queryParams = $request->getQueryParams();

        $applicationIds = $queryParams['applicationId'] ?? [];
        $usecaseIds     = $queryParams['usecaseId']     ?? [];
        $requestIds     = $queryParams['requestId']     ?? [];
        $fileIds        = $queryParams['fileId']        ?? [];

        $viewParams = [
            'applications'   => $analyzer->getAppplications($usecaseIds),
            'applicationIds' => $applicationIds,
            'usecases'       => $analyzer->getUsecases(),
            'usecaseIds'     => $usecaseIds,
            'requests'       => $analyzer->getRequests($usecaseIds, $applicationIds),
            'requestIds'     => $requestIds,
            'files'          => $analyzer->getFiles($applicationIds, $usecaseIds, $requestIds),
            'fileIds'        => $fileIds,
            'functionCalls'  => $analyzer->getFunctionCalls($applicationIds, $usecaseIds, $requestIds, $fileIds),
        ];
        return $this->view->render($response, 'trace/calls.twig', $viewParams);
    }

    public function usecase(Request $request, Response $response, array $args): Response
    {
        $analyzer = $this->container->get(Trace::class);
        $queryParams = $request->getQueryParams();

        $applicationIds = $queryParams['applicationId'] ?? [];
        $fileIds        = $queryParams['fileId']        ?? [];
        $functionIds    = $queryParams['functionId']    ?? [];

        $viewParams = [
            'applications'   => $analyzer->getAppplications(),
            'applicationIds' => $applicationIds,
            'files'          => $analyzer->getFiles($applicationIds),
            'fileIds'        => $fileIds,
            'functions'      => $analyzer->getFunctionCallsForSelect($applicationIds, $fileIds),
            'functionIds'    => $functionIds,
            'usecases'       => $analyzer->getUsecases($fileIds, $functionIds, $applicationIds),
        ];
        return $this->view->render($response, 'trace/usecase.twig', $viewParams);
    }

    public function callerCallee(Request $request, Response $response, array $params): Response
    {
        $analyzer = $this->container->get(Trace::class);
        $queryParams = $request->getQueryParams();

        $applicationIds = $queryParams['applicationId'] ?? [];
        $usecaseIds     = $queryParams['usecaseId']     ?? [];
        $requestIds     = $queryParams['requestId']     ?? [];
        $fileIds        = $queryParams['fileId']        ?? [];
        $class          = $queryParams['class']         ?? '';
        $function       = $queryParams['function']      ?? '';

        if ($params['direction'] === 'to') {
            $calls = $analyzer->getCallees($class, $function, $applicationIds, $usecaseIds, $requestIds, $fileIds);
        } elseif ($params['direction'] === 'from') {
            $calls = $analyzer->getCallers($class, $function, $applicationIds, $usecaseIds, $requestIds, $fileIds);
        } else {
            throw new \RuntimeException('invalid direction');
        }

        $viewParams = [
            'calls'  => $calls,
        ];

        return $this->view->render($response, 'trace/callerCallee.twig', $viewParams);
    }
}
