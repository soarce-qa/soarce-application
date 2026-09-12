<?php

namespace Soarce\CodeCoverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Data\ProcessedCodeCoverageData;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Filter;
use Soarce\Analyzer\Coverage;
use Soarce\CodeCoverage\Driver\DummyDriver;

class Builder
{
    public function __construct(private \mysqli $mysqli, private Coverage $coverageAnalyzer)
    {}

    public function getCodeCoverage(int|string $applicationId): CodeCoverage
    {
        $primaryCodeCoverage = new CodeCoverage(
            new DummyDriver(),
            new Filter()
        );

        foreach ($this->coverageAnalyzer->getUsecases([], [], [$applicationId]) as $usecaseId => $usecase) {
            $rawCoverage = $this->collectCoverageFromDb($applicationId, $usecaseId);

            $rawCodeCoverageData = RawCodeCoverageData::fromXdebugWithoutPathCoverage($rawCoverage);

            $processed = new ProcessedCodeCoverageData();
            $processed->initializeUnseenData($rawCodeCoverageData);
            $processed->markCodeAsExecutedByTestCase('soarce:' . $usecase['name'], $rawCodeCoverageData);

            $codeCoverage = new CodeCoverage(
                new DummyDriver(),
                new Filter()
            );

            $codeCoverage->setData($processed);
            $primaryCodeCoverage->merge($codeCoverage);
        }

        return $primaryCodeCoverage;
    }

    public function buildV6(CodeCoverage $coverage, int|string $applicationId): string
    {
        $rawCoverage = $coverage->getData(true);

        $return = '<' . '?php' . PHP_EOL;
        $return .= '
$coverage = new SebastianBergmann\CodeCoverage\CodeCoverage;
$coverage->setData(' . var_export($rawCoverage->lineCoverage(), true) . ');';

        $tests = [];
        foreach ($this->coverageAnalyzer->getUsecases([], [], [$applicationId]) as $usecase) {
            $tests['soarce:' . $usecase['name']] = [
                'size' => 'unknown',
                'status' => 0,
            ];
        }

        $return .= PHP_EOL;
        $return .= '
$coverage->setTests(' . var_export($tests, true) . ');

$filter = $coverage->filter();
$filter->setWhitelistedFiles(';

        $files = $rawCoverage->coveredFiles();
        $files = array_combine($files, array_fill(0, count($files), true));

        $return .= var_export($files, true) . ');

return $coverage;';

        return $return;
    }


    private function collectCoverageFromDb(int $applicationId, int $usecaseId): array
    {
        $sql = "SELECT any_value(f.filename) as `filename`, c.line, max(c.covered) as `covered` 
            FROM `file` f
                JOIN coverage c ON f.id = c.file_id
                JOIN request r on c.request_id = r.id
            WHERE f.application_id = " . $applicationId . " AND r.usecase_id = " . $usecaseId . "
            GROUP BY f.filename, c.line
            ORDER BY `filename`, c.line";

        $result = $this->mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);

        $rawCoverage = [];
        foreach ($result as $row) {
            if (!isset($rawCoverage[$row['filename']])) {
                $rawCoverage[$row['filename']] = [];
            }
            $rawCoverage[$row['filename']][$row['line']] = (int)$row['covered'];
        }

        return $rawCoverage;
    }

}