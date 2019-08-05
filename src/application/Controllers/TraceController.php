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

        $applicationId = '' === $request->getParam('applicationId') ? null : $request->getParam('applicationId');
        $usecaseId     = '' === $request->getParam('usecaseId')     ? null : $request->getParam('usecaseId');
        $requestId     = '' === $request->getParam('requestId')     ? null : $request->getParam('requestId');
        $fileId        = '' === $request->getParam('fileId')        ? null : $request->getParam('fileId');

        $viewParams = [
            'applications'  => $analyzer->getAppplications($usecaseId),
            'applicationId' => $applicationId,
            'usecases'      => $analyzer->getUsecases(),
            'usecaseId'     => $usecaseId,
            'requests'      => $analyzer->getRequests($usecaseId, $applicationId),
            'requestId'     => $requestId,
            'files'         => $analyzer->getFiles($applicationId, $usecaseId, $requestId),
            'fileId'        => $fileId,
            'functionCalls' => $analyzer->getFunctionCalls($applicationId, $usecaseId, $requestId, $fileId),
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
        $this->ci->view['activeSubMenu'] = 'usecases';

        $analyzer = new Trace($this->ci);

        $applicationId = '' === $request->getParam('applicationId') ? null : $request->getParam('applicationId');
        $fileId        = '' === $request->getParam('fileId')        ? null : $request->getParam('fileId');
        $functionId    = '' === $request->getParam('functionId')    ? null : $request->getParam('functionId');

        $viewParams = [
            'applications'  => $analyzer->getAppplications(),
            'applicationId' => $applicationId,
            'files'         => $analyzer->getFiles($applicationId),
            'fileId'        => $fileId,
            'functions'     => $analyzer->getFunctionCallsForSelect($applicationId, $fileId),
            'functionId'    => $functionId,
            'usecases'      => $analyzer->getUsecases($fileId, $functionId),
        ];
        return $this->ci->view->render($response, 'trace/usecase.twig', $viewParams);
    }
}
