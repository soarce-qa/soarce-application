<?php

namespace Soarce\Application\Controllers;

use MongoDB\Driver\Server;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SebastianBergmann\CodeCoverage\Report\PHP;
use Soarce\Analyzer\Coverage;
use Soarce\CodeCoverage\Builder;
use Soarce\Config;
use Soarce\DataTables\ServersidePaginatorFilter;
use Soarce\Mvc\WebApplicationController;

class CoverageController extends WebApplicationController
{
    public function index(Request $request, Response $response, array $args): Response
    {
        $analyzer = $this->container->get(Coverage::class);
        $viewParams = [
            'applications' => $analyzer->getApplicationStats(),
        ];
        return $this->view->render($response, 'coverage/index.twig', $viewParams);
    }


    public function files(Request $request, Response $response, array $args): Response
    {
        $analyzer = $this->container->get(Coverage::class);
        $queryParams = $request->getQueryParams();

        $applicationIds = $queryParams['applicationId'] ?? [];
        $usecaseIds     = $queryParams['usecaseId']     ?? [];
        $requestIds     = $queryParams['requestId']     ?? [];

        $viewParams = [
            'applications'   => $analyzer->getAppplications($usecaseIds),
            'applicationIds' => $applicationIds,
            'usecases'       => $analyzer->getUsecases([], [], $applicationIds),
            'usecaseIds'     => $usecaseIds,
            'requests'       => $analyzer->getRequests($usecaseIds, $applicationIds),
            'requestIds'     => $requestIds,
            'services'       => $this->container->get(Config::class)->getServices(),
            'queryString'    => http_build_query($request->getQueryParams()),
        ];
        return $this->view->render($response, 'coverage/files.twig', $viewParams);
    }

    public function filesAjax(Request $request, Response $response, array $args): Response
    {
        $dtRequest = ServersidePaginatorFilter::fromPost($request->getParsedBody());
        $analyzer = $this->container->get(Coverage::class);

        $queryParams = $request->getQueryParams();

        $applicationIds = $queryParams['applicationId'] ?? [];
        $usecaseIds     = $queryParams['usecaseId']     ?? [];
        $requestIds     = $queryParams['requestId']     ?? [];

        $files = $analyzer->getFiles($applicationIds, $usecaseIds, $requestIds, $dtRequest);

        $data = [];
        foreach ($files as $file) {
            $data[] = [
                'url' => '/coverage/file/' . $file['fileId'] . '?' . http_build_query($queryParams),
                'application' => $file['applicationName'],
                'file' => $file['fileName'],
                'lines' => $file['lines'],
                'totalCoveredLines' => $file['totalCoverage'],
                'totalPercentageBar' => $file['totalCoveragePercent'],
                'totalPercentageNumber' => $file['totalCoveragePercent'],
                'filteredCoveredLines' => $file['filteredCoveredLines'],
                'filteredPercentageBar' => $file['filteredCoveredPercent'],
                'filteredPercentageNumber' => $file['filteredCoveredPercent'],
            ];
        }

        $return = new \Soarce\DataTables\Response(
            $dtRequest->getDraw(),
            $analyzer->getTotalFileCount(),
            $analyzer->getFilteredFileCount($applicationIds, $usecaseIds, $requestIds, $dtRequest),
            $data,
            ""
        );

        return $response->write($return->toJson());
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
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        die();
    }

    public function export(Request $request, Response $response, $args): Response
    {
        $codeCoverageBuilder = $this->container->get(Builder::class);

        $coverage = $codeCoverageBuilder->getCodeCoverage($args['application']);

        $queryParams = $request->getQueryParams();
        $version = $queryParams['version'] ?? 12;

        switch ($version) {
            case 6:
                $out = $codeCoverageBuilder->buildV6($coverage, $args['application']);
                break;
            case 12:
                $php = new PHP();
                $out = $php->process($coverage);
                break;
            default:
                throw new \Exception('Unsupported version ' . $version);
        }

        $response = $response->withHeader('Content-Type', 'application/text')->withHeader('Content-Disposition', 'attachment; filename="soarce-' . $args['application'] . '.cov"');
        return $response->write($out);
    }

}
