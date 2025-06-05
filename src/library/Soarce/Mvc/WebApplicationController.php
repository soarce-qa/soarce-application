<?php

namespace Soarce\Mvc;

use DI\Container;
use Slim\Views\Twig;

class WebApplicationController
{

    public function __construct(protected Container $container, protected Twig $view)
    {}
}