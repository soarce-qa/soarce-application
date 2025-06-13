<?php

namespace Soarce\Application\Cli;

use Soarce\ParallelProcessDispatcher\Dispatcher;
use Soarce\ParallelProcessDispatcher\OutputAggregator;
use Soarce\ParallelProcessDispatcher\ProcessLineOutput;
use Soarce\QueueManager;
use Soarce\Receiver\CoverageReceiver;
use Soarce\Receiver\TraceReceiver;
use Symfony\Component\Console\Output\OutputInterface;
use function Sentry\captureException;

class QueueWorkerService
{
    private OutputInterface $output;

    public function __construct(
        private readonly QueueManager $queueManager,
        private readonly CoverageReceiver $coverageReceiver,
        private readonly TraceReceiver $traceReceiver)
    {}

    public function dispatch(OutputInterface $output, int $parallel, int $total)
    {
        $this->output = $output;

        $dispatcher = new Dispatcher($parallel);
        $dispatcher->setPreserveFinishedProcesses(true);

        for ($i = 0; $i < $total; $i++) {
            $dispatcher->addProcess(new ProcessLineOutput('php -f /var/www/src/bin/cli queueWorker -w', 'worker-' . $i));
        }
        $this->output->writeln("queued $total workers, $parallel in parallel. Starting");

        $oa = new OutputAggregator($dispatcher);
        foreach ($oa -> getOutput(25000) as $worker => $processOutput) {
            $this->output->write($worker . ': ' . $processOutput);
        }

        $this->output->writeln("end of worker queue, ending");
    }


    public function run(OutputInterface $output, int $timeout, int $jobs): void
    {
        $this->output = $output;

        $runs = 0;
        while (++$runs <= $jobs) {
            try {
                $this->process($timeout);
            } catch (\Throwable $t) {
                $this->output->writeln($t->getMessage());
                captureException($t);
            }
        }
    }

    protected function process(int $timeout): void
    {
        $temp = $this->queueManager->retrieve($timeout);
        if (null === $temp) {
            $this->output->writeln('empty queue after timeout');
            return;
        }

        [$usecase, $rawInput] = $temp;

        $json = json_decode((string)$rawInput, JSON_OBJECT_AS_ARRAY, 512, JSON_THROW_ON_ERROR);

        if (!isset($json['header'], $json['payload'])) {
            throw new \RuntimeException('Data not in the right format');
        }

        switch ($json['header']['type']) {
            case 'trace':
                $this->traceReceiver->persist($usecase, $json);
                $this->output->writeln('processed trace for usecase ' . $usecase);
                return;
            case 'coverage':
                $this->coverageReceiver->persist($usecase, $json);
                $this->output->writeln('processed coverage for usecase ' . $usecase);
                return;
            default:
                throw new \RuntimeException('Unsupported header type');
        }
    }
}
