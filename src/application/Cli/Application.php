<?php

namespace Soarce\Application\Cli;

use DI\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends \Symfony\Component\Console\Application
{
    public function __construct(string $name, string $version, Container $container)
    {
        parent::__construct($name, $version);
        $this->add(new QueueWorkerCommand()->setContainer($container));
        $this->add(new ChangePathsCommand()->setContainer($container));
    }

    public function doRun(InputInterface $input, OutputInterface $output): int
    {
        return parent::doRun($input, $output);
    }
}
