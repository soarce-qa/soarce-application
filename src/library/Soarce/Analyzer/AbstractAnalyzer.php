<?php

namespace Soarce\Analyzer;

use mysqli;
use Slim\Container;

abstract class AbstractAnalyzer
{
    /** @var Container */
    protected $container;

    /** @var mysqli */
    protected $mysqli;

    /**
     * Usecase constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->mysqli = $this->container->mysqli;
    }

}
