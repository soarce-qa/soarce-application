<?php

namespace Soarce\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SebastianBergmann\CodeCoverage\Report\PHP;
use Soarce\CodeCoverage\Builder;
use Soarce\Control\Exception;
use Soarce\Control\Usecase;
use Soarce\Mvc\WebApplicationController;

class ApiController extends WebApplicationController
{
    public function index(Request $request, Response $response, array $args): Response
    {
        return $this->view->render($response, 'api/index.twig');
    }

    public function usecase(Request $request, Response $response, $args): Response
    {
        $usecaseControl = $this->container->get(Usecase::class);
        $usecase = $args['usecase'] ?? '';
        $action = $args['action'] ?? '';

        if ($request->getMethod() === 'GET') {
            if ($usecase === '') { // whole list
                return $this->returnJson($response, $usecaseControl->getAllUsecases());
            }
            return $this->returnJson($response, $usecaseControl->getUsecase($usecase));
        }

        if ($request->getMethod() === 'POST') {
            if ('' === $usecase) {
                if (trim((string)$request->getBody()) === '') {
                    return $this->returnJson($response, ['message' => 'empty request'], 400);
                }

                $jsonDecoded = json_decode((string)$request->getBody(), JSON_OBJECT_AS_ARRAY, 512, JSON_THROW_ON_ERROR);
                if (!isset($jsonDecoded['usecase']) || trim($jsonDecoded['usecase']) === '') {
                    return $this->returnJson($response, ['message' => 'no usecase name provided'], 400);
                }
                $usecase = $jsonDecoded['usecase'];
            }

            if ('' === $action) {
                try {
                    $usecaseControl->create($usecase);
                    return $this->returnJson($response, ['message' => 'created'], 201);
                } catch (Exception $e) {
                    return $this->returnJson($response, ['message' => $e->getMessage()], $e->getCode());
                } catch (\Throwable $t) {
                    return $this->returnJson($response, ['message' => $t->getMessage()], 500);
                }
            }

            if ('activate' === $action) {
                $usecaseControl->activate($usecase);
                return $this->returnJson($response, ['message' => 'activate'], 201);
            }

            if ('restart' === $action) {
                $usecaseControl->restart($usecase);
                return $this->returnJson($response, ['message' => 'restarted'], 201);
            }

            if ('createOrRestart' === $action) {
                $usecaseControl->createOrRestart($usecase);
                return $this->returnJson($response, ['message' => 'created or restarted'], 201);
            }
        }

        if ($request->getMethod() === 'DELETE') {
            $usecaseControl->delete($usecase);
            return $this->returnJson($response, ['message' => 'deleted'], 204);
        }

        return $this->returnJson($response, ['message' => 'method not allowed: ' . $request->getMethod()], 405);
    }

    private function returnJson(Response $response, mixed $payload, int $code = 200): Response
    {
        $response = $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        return $response->write(json_encode($payload, JSON_PRETTY_PRINT));
    }

    public function coverage(Request $request, Response $response, $args): Response
    {
        $codeCoverageBuilder = $this->container->get(Builder::class);

        $php = new PHP();

        $response = $response->withHeader('Content-Type', 'application/text')->withHeader('Content-Disposition', 'attachment; filename="soarce-' . $args['application'] . '.cov"');
        return $response->write($php->process($codeCoverageBuilder->getCodeCoverage($args['application'])));
    }
}
