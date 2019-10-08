<?php

namespace Soarce\Application\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Container;
use Soarce\Analyzer\Trace;

class TraceController
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
        $this->ci->view['activeMainMenu'] = 'traces';
    }

    /**
     * @param  Request  $request
     * @param  Response $response
     * @return Response
     */
    public function index(Request $request, Response $response): Response
    {
        return $this->ci->view->render($response, 'trace/index.twig');
    }

    /**
     * @param  Request  $request
     * @param  Response $response
     * @return Response
     */
    public function calls(Request $request, Response $response): Response
    {
        $this->ci->view['activeSubMenu'] = 'calls';

        $analyzer = new Trace($this->ci);

        $applicationIds = $request->getParam('applicationId') ?? [];
        $usecaseIds     = $request->getParam('usecaseId')     ?? [];
        $requestIds     = $request->getParam('requestId')     ?? [];
        $fileIds        = $request->getParam('fileId')        ?? [];

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
        return $this->ci->view->render($response, 'trace/calls.twig', $viewParams);
    }

    /**
     * @param  Request  $request
     * @param  Response $response
     * @return Response
     */
    public function usecase(Request $request, Response $response): Response
    {
        $this->ci->view['activeSubMenu'] = 'trace-usecases';

        $analyzer = new Trace($this->ci);

        $applicationIds = $request->getParam('applicationId') ?? [];
        $fileIds        = $request->getParam('fileId')        ?? [];
        $functionIds    = $request->getParam('functionId')    ?? [];

        $viewParams = [
            'applications'   => $analyzer->getAppplications(),
            'applicationIds' => $applicationIds,
            'files'          => $analyzer->getFiles($applicationIds),
            'fileIds'        => $fileIds,
            'functions'      => $analyzer->getFunctionCallsForSelect($applicationIds, $fileIds),
            'functionIds'    => $functionIds,
            'usecases'       => $analyzer->getUsecases($fileIds, $functionIds, $applicationIds),
        ];
        return $this->ci->view->render($response, 'trace/usecase.twig', $viewParams);
    }

    /**
     * @param  Request  $request
     * @param  Response $response
     * @param  array    $params
     * @return Response
     */
    public function callerCallee(Request $request, Response $response, $params): Response
    {
        $analyzer = new Trace($this->ci);

        $applicationIds = $request->getParam('applicationId') ?? [];
        $usecaseIds     = $request->getParam('usecaseId')     ?? [];
        $requestIds     = $request->getParam('requestId')     ?? [];
        $fileIds        = $request->getParam('fileId')        ?? [];
        $class          = $request->getParam('class')         ?? '';
        $function       = $request->getParam('function')      ?? '';

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

        return $this->ci->view->render($response, 'trace/callerCallee.twig', $viewParams);
    }
}
