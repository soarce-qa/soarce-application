<?php

namespace Soarce\Control;

use Soarce\Config\Service as ServiceConfig;
use Soarce\Control\Service\Actionable;

class Service
{
    /** @var ServiceConfig[] */
    protected $serviceConfig;

    public function __construct()
    {
        $this->serviceConfig = $this->container->settings['soarce']['services'];  //@TODO
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

    /**
     * @return string[]
     */
    public function end(): array
    {
        $ret = [];
        foreach ($this->getAllServiceActionables() as $serviceName => $actionable) {
            $ret[$serviceName] = $actionable->end();
        }
        return $ret;
    }
}
