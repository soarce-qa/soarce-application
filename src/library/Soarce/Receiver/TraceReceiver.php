<?php

namespace Soarce\Receiver;

class TraceReceiver extends ReceiverAbstract
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

        foreach ($payload as $filename => $functions) {
            $this->storeFunctionCallsForOneFile($filename, $functions);
        }
    }

    /**
     * @param string $filename
     * @param int[]  $functions
     */
    private function storeFunctionCallsForOneFile($filename, $functions): void
    {
        if (strpos($filename, "eval()'d code") !== false) {
            return;
        }

        $sql = 'INSERT IGNORE INTO `function_call` (`file_id`, `request_id`, `class`, `function`, `type`, `calls`, `walltime`) VALUES ';
        $fileId = $this->createFile($filename);
        $requestId = $this->getRequestId();

        $rows = [];
        foreach ($functions as $functionName => $info) {
            $split = preg_split('/(->|::)/', $functionName);
            if (count($split) === 1) {
                $rows[] = "({$fileId}, {$requestId}, '', '"
                    . mysqli_real_escape_string($this->mysqli, $split[0])
                    . "', '"
                    . (1 == $info['type'] ? 'user-defined' : 'internal')
                    . "', '"
                    . mysqli_real_escape_string($this->mysqli, $info['count'])
                    . "', '"
                    . mysqli_real_escape_string($this->mysqli, $info['walltime'])
                    . "')";
                continue;
            }

            $rows[] = "({$fileId}, {$requestId}, '"
                . mysqli_real_escape_string($this->mysqli, $split[0])
                . "', '"
                . mysqli_real_escape_string($this->mysqli, $split[1])
                . "', '"
                . (1 == $info['type'] ? 'user-defined' : 'internal')
                . "', '"
                . mysqli_real_escape_string($this->mysqli, $info['count'])
                . "', '"
                . mysqli_real_escape_string($this->mysqli, $info['walltime'])
                . "')";
        }

        $sql .= implode(', ', $rows);

        $this->mysqli->query($sql);
    }
}
