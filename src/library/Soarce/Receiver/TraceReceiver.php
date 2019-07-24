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

        $sql = 'INSERT INTO `function_calls` (`application_id`, `file_id`, `class`, `function`, `type`) VALUES ';
        $fileId = $this->createFile($filename);

        $rows = [];

        foreach ($functions as $functionName => $userDefined) {
            $split = preg_split('/(->|::)/', $functionName);
            if (count($split) === 1) {
                $rows[] = "({$this->getApplicationId()}, {$fileId}, '', '"
                    . mysqli_real_escape_string($this->mysqli, $split[0])
                    . "', '"
                    . (1 == $userDefined ? 'user-defined' : 'internal')
                    . "')";
                continue;
            }

            $rows[] = "({$this->getApplicationId()}, {$fileId}, '"
                . mysqli_real_escape_string($this->mysqli, $split[0])
                . "', '"
                . mysqli_real_escape_string($this->mysqli, $split[1])
                . "', '"
                . (1 == $userDefined ? 'user-defined' : 'internal')
                . "')";
        }

        $sql .= implode(', ', $rows);

        $this->mysqli->query($sql);
    }
}
