<?php

namespace Soarce\Application\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Container;
use Soarce\Control\Service;
use Soarce\Control\Usecase;

class ControlController
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
        $this->ci->view['activeMainMenu'] = 'control';
    }

    /**
     * @param  Request $request
     * @param  Response $response
     * @return Response
     */
    public function index(Request $request, Response $response): Response
    {
        return $this->ci->view->render($response, 'control/index.twig');
    }

    /**
     * @param  Request  $request
     * @param  Response $response
     * @param  array    $args
     * @return Response
     */
    public function service(Request $request, Response $response, $args): Response
    {
        $this->ci->view['activeSubMenu'] = 'services';

        $serviceControl = new Service($this->ci);
        $service = $args['service'] ?? '';
        $action  = $request->getParam('action');

        if ($request->isPost()) {
            switch ($action) {
                case 'preconditions':
                    $this->ci->view['services'] = $serviceControl->checkPreconditons();
                    return $this->ci->view->render($response, 'control/preconditions.twig');
                case 'details':
                    $this->ci->view['service'] = $serviceControl->getServiceActionable($service);
                    return $this->ci->view->render($response, 'control/details.twig');
                case 'start':
                    $this->ci->view['services'] = $serviceControl->start();
                    return $this->ci->view->render($response, 'control/startstop.twig');
                    break;
                case 'end':
                    $this->ci->view['services'] = $serviceControl->end();
                    return $this->ci->view->render($response, 'control/startstop.twig');
                    break;
            }
        }

        $this->ci->view['services'] = $serviceControl->getAllServiceActionables();

        return $this->ci->view->render($response, 'control/service.twig');
    }

    /**
     * @param  Request  $request
     * @param  Response $response
     * @param  array    $args
     * @return Response
     */
    public function usecase(Request $request, Response $response, $args): Response
    {
        $this->ci->view['activeSubMenu'] = 'control-usecases';

        $usecaseControl = new Usecase($this->ci);
        $usecase = $args['usecase'] ?? '';
        $action  = $request->getParam('action');

        if (null !== $usecase && $request->isPost()) {
            switch ($action) {
                case 'create':
                    $usecaseControl->create($request->getParam('usecase'));
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

        $this->ci->view['usecases'] = $usecaseControl->getAllUsecases();

        return $this->ci->view->render($response, 'control/usecase.twig');
    }
}
