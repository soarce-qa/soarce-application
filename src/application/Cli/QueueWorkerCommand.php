<?php

namespace Soarce\Application\Cli;

use DI\Container;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->addOption('isWorker', 'w', InputOption::VALUE_NONE, 'Specify if this is the worker or dispatcher')
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

        if ($input->getOption('isWorker')) {
            $QueueWorker->run(
                $output,
                $_ENV['SOARCE_QUEUE_TIMEOUT_SECONDS'] ?? 15,
                $_ENV['SOARCE_JOBS_PER_WORKER'] ?? 500,
            );
        } else {
            $QueueWorker->dispatch(
                $output,
                $_ENV['SOARCE_WORKERS_PARALLEL'],
                $_ENV['SOARCE_WORKERS_TOTAL'],
            );
        }

        return 0;
    }
}
