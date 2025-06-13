<?php

namespace Soarce\Application\Cli;

use DI\Container;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueWorkerCommand extends CommandAlias
{
    private Container $container;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('queueWorker')
            ->setDescription('Processes the queue serially to allow the web application to exit early and prevent racing conditions');
    }

    public function setContainer(Container $container): self
    {
        $this->container = $container;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $QueueWorker = $this->container->get(QueueWorkerService::class);

        $QueueWorker->run($output);

        return 0;
    }
}
