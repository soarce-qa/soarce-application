<?php

namespace Soarce\Receiver;

class CoverageReceiver extends ReceiverAbstract
{
    /** @var string[] */
    private array $fileMd5Hashes = [];

    public function persist(int $usecaseId, array $json): void
    {
        $header  = $json['header'];
        $payload = $json['payload'];
        $this->fileMd5Hashes = $json['md5'] ?? [];

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
            $this->storeCoverageForOneFile($filename, $coveredLines);
        }
    }

    /**
     * @param string $filename
     * @param int[] $coveredLines
     */
    private function storeCoverageForOneFile(string $filename, array $coveredLines): void
    {
        if (str_contains($filename, "eval()'d code")) {
            return;
        }

        $fileId = $this->createFile($filename, count($coveredLines), $this->fileMd5Hashes[$filename] ?? null);
        $requestId = $this->getRequestId();

        $rows = [];
        foreach ($coveredLines as $line => $covered) {
            $rows[] = "({$fileId}, {$requestId}, {$line}, {$covered})";
        }

        $sql = 'INSERT IGNORE INTO `coverage` (`file_id`, `request_id`, `line`, `covered`) VALUES ';
        $sql .= implode(', ', $rows);

        $this->mysqli->query($sql);
    }
}
