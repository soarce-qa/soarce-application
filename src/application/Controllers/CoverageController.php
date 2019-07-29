<?php

namespace Soarce\Application\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Container;
use Soarce\Analyzer\Coverage;

class CoverageController
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
    }

    /**
	 * @param  Request  $request
	 * @param  Response $response
	 * @return Response
	 */
  	public function index(Request $request, Response $response): Response
    {
        $analyzer = new Coverage($this->ci);

        $applicationId = '' === $request->getParam('applicationId') ? null : $request->getParam('applicationId');
        $usecaseId     = '' === $request->getParam('usecaseId')     ? null : $request->getParam('usecaseId');
        $requestId     = '' === $request->getParam('requestId')     ? null : $request->getParam('requestId');

        $viewParams = [
            'applications'  => $analyzer->getAppplications($usecaseId),
            'applicationId' => $applicationId,
            'usecases'      => $analyzer->getUsecases(),
            'usecaseId'     => $usecaseId,
            'requests'      => $analyzer->getRequests($usecaseId, $applicationId),
            'requestId'     => $requestId,
            'files'         => $analyzer->getFiles($applicationId, $usecaseId, $requestId),
            'services'      => $this->ci->settings['soarce']['services'],
        ];
        return $this->ci->view->render($response, 'coverage/index.twig', $viewParams);
	}

    /**
     * @param  Request  $request
     * @param  Response $response
     * @param  array    $params
     * @return Response
     */
	public function file(Request $request, Response $response, $params): Response
    {
        $analyzer = new Coverage($this->ci);

        $usecaseId     = '' === $request->getParam('usecaseId')     ? null : $request->getParam('usecaseId');
        $requestId     = '' === $request->getParam('requestId')     ? null : $request->getParam('requestId');
        $fileId        = (int)($params['file'] ?? 0);

        if (0 === $fileId) {
            throw new \InvalidArgumentException('needs a fileId');
        }

        $viewParams = [
            'source'        => $analyzer->getSource($fileId),
            'coverage'      => $analyzer->getCoverage($fileId, $usecaseId, $requestId),
            'usecases'      => $analyzer->getUsecases(),
            'usecaseId'     => $usecaseId,
            'requests'      => $analyzer->getRequests($usecaseId),
            'requestId'     => $requestId,
        ];
        return $this->ci->view->render($response, 'coverage/file.twig', $viewParams);
    }
}
