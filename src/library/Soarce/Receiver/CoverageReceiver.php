<?php

namespace Soarce\Receiver;

use Slim\Container;

class CoverageReceiver extends ReceiverAbstract
{
    /**
     * @param array $json
     */
    public function persist($json): void
    {
        $header  = $json['header'];
        $payload = $json['payload'];

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
        $sql = 'INSERT INTO `coverage` (`application_id`, `file_id`, `line`) VALUES ';
        $fileId = $this->createFile($filename);

        $rows = [];

        foreach (array_keys($coveredLines) as $line) {
            $rows[] = "({$this->getApplicationId()}, {$fileId}, {$line})";
        }

        $sql .= implode(', ', $rows);

        $this->mysqli->query($sql);
    }
}
