<?php

namespace Soarce\Receiver;

class CoverageReceiver extends ReceiverAbstract
{
    /** @var string[] */
    private $fileMd5Hashes;

    /**
     * @param array $json
     */
    public function persist($json): void
    {
        $header  = $json['header'];
        $payload = $json['payload'];
        $this->fileMd5Hashes = $json['md5'];

        $this->createApplication($header['host']);

        $this->createRequest(
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
     * @todo create cronjob that removes duplicate rows in coverage!
     *
     * @param string $filename
     * @param int[]  $coveredLines
     */
    private function storeCoverageForOneFile($filename, $coveredLines): void
    {
        if (strpos($filename, "eval()'d code") !== false) {
            return;
        }

        $sql = 'INSERT INTO `coverage` (`file_id`, `line`) VALUES ';
        if (isset($this->fileMd5Hashes[$filename])) {
            $fileId = $this->createFile($filename, $this->fileMd5Hashes[$filename]);
        } else {
            $fileId = $this->createFile($filename);
        }

        $rows = [];
        foreach (array_keys($coveredLines) as $line) {
            $rows[] = "({$fileId}, {$line})";
        }

        $sql .= implode(', ', $rows);

        $this->mysqli->query($sql);
    }
}
