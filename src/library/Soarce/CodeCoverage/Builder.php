<?php

namespace Soarce\CodeCoverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Data\ProcessedCodeCoverageData;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Filter;
use Soarce\CodeCoverage\Driver\DummyDriver;

class Builder
{
    public function __construct(private \mysqli $mysqli)
    {}

    public function getCodeCoverage(int $applicationId): CodeCoverage
    {
        $rawCoverage = $this->collectCoverageFromDb($applicationId);

        $driver = new DummyDriver();

        $filter = new Filter();
        $reflection = new \ReflectionClass($filter);

        $files = array_keys($rawCoverage);
        $files = array_combine($files, array_fill(0, count($files), true));

        $reflection->getProperty('files')->setValue($filter, $files);
        $reflection->getProperty('isFileCache')->setValue($filter, $files);

        $codeCoverage = new CodeCoverage($driver, $filter);

        $rawCodeCoverageData = RawCodeCoverageData::fromXdebugWithoutPathCoverage($rawCoverage);

        $processed = new ProcessedCodeCoverageData();
        $processed->initializeUnseenData($rawCodeCoverageData);
        $processed->markCodeAsExecutedByTestCase('soarce', $rawCodeCoverageData);

        $codeCoverage->setData($processed);
        return $codeCoverage;
    }

    private function collectCoverageFromDb(int $applicationId): array
    {
        $sql = "SELECT any_value(f.filename) as `filename`, c.line, max(c.covered) as `covered` 
            FROM `file` f JOIN coverage c ON f.id = c.file_id
            WHERE f.application_id = " . $applicationId . "
            GROUP BY c.line
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