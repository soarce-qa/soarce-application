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
        $this->ci->view['activeMainMenu'] = 'coverage';
    }

    /**
     * @param  Request  $request
     * @param  Response $response
     * @return Response
     */
    public function index(Request $request, Response $response): Response
    {
        $analyzer = new Coverage($this->ci);

        $applicationIds = $request->getParam('applicationId') ?? [];
        $usecaseIds     = $request->getParam('usecaseId')     ?? [];
        $requestIds     = $request->getParam('requestId')     ?? [];

        $viewParams = [
            'applications'   => $analyzer->getAppplications($usecaseIds),
            'applicationIds' => $applicationIds,
            'usecases'       => $analyzer->getUsecases(),
            'usecaseIds'     => $usecaseIds,
            'requests'       => $analyzer->getRequests($usecaseIds, $applicationIds),
            'requestIds'     => $requestIds,
            'files'          => $analyzer->getFiles($applicationIds, $usecaseIds, $requestIds),
            'services'       => $this->ci->settings['soarce']['services'],
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

        $applicationIds = $request->getParam('applicationId') ?? [];
        $usecaseIds     = $request->getParam('usecaseId')     ?? [];
        $requestIds     = $request->getParam('requestId')     ?? [];
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
        return $this->ci->view->render($response, 'coverage/file.twig', $viewParams);
    }

    /**
     * @param  Request  $request
     * @param  Response $response
     * @param  array    $params
     * @return Response
     */
    public function line(Request $request, Response $response, $params): Response
    {
        $analyzer = new Coverage($this->ci);

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
