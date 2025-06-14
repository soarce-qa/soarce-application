<?php

namespace Soarce\Application\Cli;

use DI\Container;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ChangePathsCommand extends CommandAlias
{
    private Container $container;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('changePaths')
            ->addOption('search', 's',  InputOption::VALUE_OPTIONAL, 'Specify the beginning of the path that needs replacement, can be empty', '')
            ->addOption('replace', 'r', InputOption::VALUE_OPTIONAL, 'Specify what the path will be replaced with, can be empty', '')
            ->addArgument('infile',     InputArgument::REQUIRED,     'Specify the file to read')
            ->addArgument('outfile',    InputArgument::REQUIRED,     'Specify the filename to output to')
            ->setDescription('Replace parts in the file paths of the coverage file to merge multiple cov files accurately.');
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
        $changePathService = $this->container->get(ChangePathsService::class);

        $changePathService->run(
            $output,
            $input->getOption('search'),
            $input->getOption('replace'),
            $input->getArgument('infile'),
            $input->getArgument('outfile')
        );

        return 0;
    }
}
