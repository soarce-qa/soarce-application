<?php

namespace Soarce\Application\Cli;

use Soarce\QueueManager;
use Soarce\Receiver\CoverageReceiver;
use Soarce\Receiver\TraceReceiver;
use Symfony\Component\Console\Output\OutputInterface;
use function Sentry\captureException;

class QueueWorkerService
{
    private const int RESTART_AFTER_RUNS = 500;

    private OutputInterface $output;

    public function __construct(
        private readonly QueueManager $queueManager,
        private readonly CoverageReceiver $coverageReceiver,
        private readonly TraceReceiver $traceReceiver)
    {}

    public function run(OutputInterface $output): void
    {
        $this->output = $output;

        $runs = 0;
        while (++$runs < self::RESTART_AFTER_RUNS) {
            try {
                $this->process();
            } catch (\Throwable $t) {
                $this->output->writeln($t->getMessage());
                captureException($t);
            }
        }
    }

    protected function process(): void
    {
        $temp = $this->queueManager->retrieve();
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
