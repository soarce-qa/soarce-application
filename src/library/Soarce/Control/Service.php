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
        foreach ($this->getAllServiceConfigs() as $service) {
            $actionables[] = new Actionable($service);
        }

        return $actionables;
    }


}