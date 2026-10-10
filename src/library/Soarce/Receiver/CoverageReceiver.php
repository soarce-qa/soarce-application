<?php

namespace Soarce\Receiver;

use Soarce\Config;
use Soarce\Filter\PathFilter;

class CoverageReceiver extends ReceiverAbstract
{
    /** @var string[] */
    private array $fileMd5Hashes = [];

    public function persist(int $usecaseId, array $json): void
    {
        $header  = $json['header'];
        $payload = $json['payload'];
        $this->fileMd5Hashes = $json['md5'] ?? [];

        $serviceConfig = $this->config->getService($header['host']);
        $filter = PathFilter::fromServiceConfig($serviceConfig);
        $payload = $filter->filterArrayKeys($payload);

        $this->createApplication($header['host']);

        $this->createRequest(
            $usecaseId,
            $header['request_id'],
            $header['request_time'],
            $header['get'],
            $header['post'],
            $header['server'],
            $header['env']
        );

        foreach ($payload as $filename => $coveredLines) {
            $this->storeCoverageForOneFile($filename, $coveredLines, $serviceConfig->getFilters()['skip_unused_lines'] ?? false);
        }
    }

    /**
     * @param int[] $coveredLines
     */
    private function storeCoverageForOneFile(string $filename, array $coveredLines, bool $skipUnusedLines = false): void
    {
        if (str_contains($filename, "eval()'d code")) {
            return;
        }

        $fileId = $this->createFile($filename, count($coveredLines), $this->fileMd5Hashes[$filename] ?? null);
        $requestId = $this->getRequestId();

        $rows = [];
        foreach ($coveredLines as $line => $covered) {
            if ($skipUnusedLines && $covered < 1) {
                continue;
            }
            $rows[] = "({$fileId}, {$requestId}, {$line}, {$covered})";
        }

        if ($rows !== []) {
            $sql = 'INSERT IGNORE INTO `coverage` (`file_id`, `request_id`, `line`, `covered`) VALUES ';
            $sql .= implode(', ', $rows);
            $this->mysqli->query($sql);
        }
    }

    public function updateMaterializedView(int $usecaseId): void
    {
        sleep(2); // to prevent racing conditions, when another worker might still process incoming coverage of the same usecase.
        $requestIds = $this->mysqli->query("SELECT id FROM request WHERE usecase_id = {$usecaseId}")->fetch_all(MYSQLI_ASSOC);
        $requestIds = array_column($requestIds, 'id');

        $fileIds = $this->mysqli->query("SELECT DISTINCT file_id FROM coverage WHERE request_id IN (" . implode(", ", $requestIds) . ")")->fetch_all(MYSQLI_ASSOC);
        $fileIds = array_column($fileIds, 'file_id');

        $chunks = array_chunk($fileIds, 1000);
        foreach ($chunks as $chunk) {
            $this->updateMaterializedViewBatch($chunk);
        }
    }

    private function updateMaterializedViewBatch(array $fileIds): void
    {
        if ($fileIds === []) {
            return;
        }

        $sql = "UPDATE file f
            set f.total_coverage = (SELECT count(distinct c.line) from coverage c where c.file_id = f.id and c.covered=1),
                f.`lines` = (SELECT count(distinct c.line) from coverage c where c.file_id = f.id)
            where f.id in (" . implode(", ", $fileIds) . ")";
        $this->mysqli->query($sql);

        $sql = "UPDATE file f set total_coverage_percent = (f.total_coverage / f.lines) * 100 where f.id in (" . implode(", ", $fileIds) . ")";
        $this->mysqli->query($sql);
    }
}
