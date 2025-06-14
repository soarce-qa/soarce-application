<?php

namespace Soarce\Application\Cli;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\PHP;
use Symfony\Component\Console\Output\OutputInterface;

class ChangePathsService
{
    private OutputInterface $output;

    public function __construct()
    {}

    public function run(OutputInterface $output, string $search, string $replace, string $infile, string $outfile): void
    {
        $this->output = $output;

        /** @var CodeCoverage $coverage */
        $coverage = require($infile);

        $processedCodeCoverageData = $coverage->getData(true);

        foreach($processedCodeCoverageData->coveredFiles() as $coveredFile) {
            if (!str_starts_with($coveredFile, $search)) {
                continue;
            }
            //not using str_replace so that we can make sure to replace the start of the string only!

            $newName = $replace . substr($coveredFile, strlen($search));
            $processedCodeCoverageData->renameFile($coveredFile, $newName);
        }

        $serializer = new PHP();
        $serializer->process($coverage, $outfile);
    }
}
