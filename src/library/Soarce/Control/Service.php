<?php

namespace Soarce\Control;

use Slim\Container;
use Soarce\Config\Service as ServiceConfig;
use Soarce\Control\Service\Actionable;

class Service
{
    /** @var Container */
    protected $container;

    /** @var ServiceConfig[] */
    protected $serviceConfig;

    /**
     * Usecase constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->serviceConfig = $this->container->settings['soarce']['services'];
    }

    /**
     * @return ServiceConfig[]
     */
    public function getAllServiceConfigs(): array
    {
        return $this->serviceConfig;
    }

    /**
     * @return Actionable[]
     */
    public function getAllServiceActionables(): array
    {
        $actionables = [];
        foreach ($this->getAllServiceConfigs() as $serviceName => $service) {
            $actionables[$serviceName] = new Actionable($service);
        }

        return $actionables;
    }

    /**
     * @param  string     $serviceName
     * @return Actionable
     */
    public function getServiceActionable($serviceName): Actionable
    {
        return new Actionable($this->getAllServiceConfigs()[$serviceName]);
    }

    /**
     * @return Actionable[]
     */
    public function checkPreconditons(): array
    {
        $actionables = [];
        foreach ($this->getAllServiceActionables() as $serviceName => $actionable) {
            $actionable->collectPreconditions();
            $actionables[$serviceName] = $actionable;
        }

        return $actionables;
    }

    /**
     * @return string[]
     */
    public function start(): array
    {
        $ret = [];
        foreach ($this->getAllServiceActionables() as $serviceName => $actionable) {
            $ret[$serviceName] = $actionable->start();
        }
        return $ret;
    }
}
