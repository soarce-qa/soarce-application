<?php

namespace Soarce\Application\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Container;

class RequestController
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
        $this->ci->view['activeMainMenu'] = 'requests';
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
    public function overview(Request $request, Response $response): Response
    {
        $this->ci->view['activeSubMenu'] = 'overview';

        $analyzer = new \Soarce\Analyzer\Request($this->ci);

        $applicationIds = $request->getParam('applicationId') ?? [];
        $usecaseIds     = $request->getParam('usecaseId')     ?? [];

        $viewParams = [
            'applications'   => $analyzer->getAppplications($usecaseIds),
            'applicationIds' => $applicationIds,
            'usecases'       => $analyzer->getUsecases(),
            'usecaseIds'     => $usecaseIds,
            'requests'       => $analyzer->getRequestsOverview($usecaseIds, $applicationIds),
        ];
        return $this->ci->view->render($response, 'request/overview.twig', $viewParams);
    }

    /**
     * @param  Request  $request
     * @param  Response $response
     * @param  array    $params
     * @return Response
     */
    public function request(Request $request, Response $response, $params): Response
    {
        $this->ci->view['activeSubMenu'] = 'overview';

        $analyzer = new \Soarce\Analyzer\Request($this->ci);

        $applicationIds = $request->getParam('applicationId') ?? [];
        $usecaseIds     = $request->getParam('usecaseId')     ?? [];
        $requestId      = (int)($params['request'] ?? 0);

        if (0 === $requestId) {
            throw new \InvalidArgumentException('needs a requestId');
        }

        $viewParams = [
            'applicationIds' => $applicationIds,
            'usecaseIds'     => $usecaseIds,
            'request'        => $analyzer->getRequest($requestId),
        ];
        return $this->ci->view->render($response, 'request/request.twig', $viewParams);
    }

    /**
     * @param  Request  $request
     * @param  Response $response
     * @param  array    $params
     * @return Response
     */
    public function sequence(Request $request, Response $response, $params): Response
    {
        $this->ci->view['activeSubMenu'] = 'overview';

        $analyzer = new \Soarce\Analyzer\Request($this->ci);
        $requestId = (int)($params['request'] ?? 0);

        if (0 === $requestId) {
            throw new \InvalidArgumentException('needs a requestId');
        }

        $originalRequest = $analyzer->getRequest($requestId);
        $requests        = $analyzer->getSequence($originalRequest['request_id']);

        $viewParams = [
            'originalRequest' => $originalRequest,
            'requests'        => $requests,
            'applications'    => array_unique(array_column($originalRequest, 'application')),
        ];

        return $this->ci->view->render($response, 'request/sequence.twig', $viewParams);
    }
}
