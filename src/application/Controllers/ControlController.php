<?php

namespace Soarce\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Soarce\Control\Service;
use Soarce\Control\Usecase;
use Soarce\Mvc\WebApplicationController;

class ControlController extends WebApplicationController
{
    public function index(Request $request, Response $response, array $args): Response
    {
        return $this->view->render($response, 'control/index.twig');
    }

    /**
     * @param  Request  $request
     * @param  Response $response
     * @param  array    $args
     * @return Response
     */
    public function service(Request $request, Response $response, $args): Response
    {
        $serviceControl = $this->container->get(Service::class);
        $service = $args['service'] ?? '';
        $postParams = $request->getParsedBody();
        $action  = $postParams['action'] ?? '';

        if ($request->getMethod() === 'POST') {
            switch ($action) {
                case 'preconditions':
                    return $this->view->render(
                        $response,
                        'control/preconditions.twig',
                        ['services' => $serviceControl->checkPreconditions()]
                    );
                case 'details':
                    return $this->view->render(
                        $response,
                        'control/details.twig',
                        ['services' => $serviceControl->getServiceActionable($service)]
                    );
                case 'start':
                    return $this->view->render(
                        $response,
                        'control/startstop.twig',
                        ['services' => $serviceControl->start()]
                    );
                case 'end':
                    return $this->view->render(
                        $response,
                        'control/startstop.twig',
                        ['services' => $serviceControl->end()]
                    );
            }
        }

        return $this->view->render(
            $response,
            'control/service.twig',
            ['services' => $serviceControl->getAllServiceActionables()],
        );
    }

    /**
     * @param  Request  $request
     * @param  Response $response
     * @param  array    $args
     * @return Response
     */
    public function usecase(Request $request, Response $response, $args): Response
    {
        $usecaseControl = $this->container->get(Usecase::class);
        $usecase = $args['usecase'] ?? '';

        $postParams = $request->getParsedBody();
        $action  = $postParams['action'] ?? '';

        if ($request->getMethod() === 'POST') {
            switch ($action) {
                case 'create':
                    $usecaseControl->create($postParams['usecase']);
                    break;
                case 'activate':
                    $usecaseControl->activate($usecase);
                    break;
                case 'restart':
                    $usecaseControl->restart($usecase);
                    break;
                case 'delete':
                    $usecaseControl->delete($usecase);
                    break;
            }
        }

        $viewParams = [
            'usecases' => $usecaseControl->getAllUsecases(),
        ];


        return $this->view->render($response, 'control/usecase.twig', $viewParams);
    }
}
