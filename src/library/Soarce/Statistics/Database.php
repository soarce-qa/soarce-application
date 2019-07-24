<?php

namespace Soarce\Statistics;

use Slim\Container;

class Database
{
    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     *
     */
    public function getStats()

}
