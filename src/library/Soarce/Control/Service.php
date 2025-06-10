<?php

namespace Soarce\Control;

use Soarce\Config;
use Soarce\Control\Service\Actionable;

class Service
{
    public function __construct(private Config $config)
    {
    }

    /**
     * @return Actionable[]
     */
    public function getAllServiceActionables(): array
    {
        $actionables = [];
        foreach ($this->config->getServices() as $serviceName => $service) {
            $actionables[$serviceName] = new Actionable($service);
        }

        return $actionables;
    }

    /**
     * @param  string     $serviceName
     * @return Actionable
     */
    public function getServiceActionable(string $serviceName): Actionable
    {
        return new Actionable($this->config->getService($serviceName));
    }

    /**
     * @return Actionable[]
     */
    public function checkPreconditions(): array
    {
        $actionables = [];
        foreach ($this->getAllServiceActionables() as $serviceName => $actionable) {
            if ($actionable->ping()) {
                $actionable->collectPreconditions();
            }
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
