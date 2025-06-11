<?php

namespace Soarce\Receiver;

class TraceReceiver extends ReceiverAbstract
{
    public function persist(array $json): void
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

        foreach ($payload['functions'] as $filename => $functions) {
            $this->storeFunctionCallsForOneFile($filename, $functions);
        }

        $this->storeFunctionCallMap($payload['calls']);
    }

    /**
     * @param int[][] $map
     */
    private function storeFunctionCallMap(array $map): void
    {
        $functionIdMap = $this->getFunctionsIdMap();

        $rows = [];
        foreach ($map as $caller => $callees) {
            if (!isset($functionIdMap[$caller])) {
                continue;
            }
            $callerId = $functionIdMap[$caller];
            foreach ($callees as $callee => $calls) {
                if (!isset($functionIdMap[$callee])) {
                    continue;
                }
                $calleeId = $functionIdMap[$callee];
                $rows[] = "('{$callerId}', '{$calleeId}', '{$calls}')";
            }
        }

        $sqlStart = 'INSERT IGNORE INTO `function_map` (`caller`, `callee`, `calls`) VALUES ';
        foreach (array_chunk($rows, 1000) as $block) {
            $this->mysqli->query($sqlStart . implode(',', $block));
        }
    }

    /**
     * @return int[]
     */
    private function getFunctionsIdMap(): array
    {
        $requestId = $this->getRequestId();
        $sql = "SELECT `id`, `number` FROM `function_call` WHERE `request_id` = {$requestId};";
        $result = $this->mysqli->query($sql);

        return array_column($result->fetch_all(MYSQLI_ASSOC), 'id', 'number');
    }

    /**
     * @param string $filename
     * @param array[] $functions
     */
    private function storeFunctionCallsForOneFile(string $filename, array $functions): void
    {
        if (str_contains($filename, "eval()'d code")) {
            return;
        }

        $fileId = $this->createFile($filename);
        $requestId = $this->getRequestId();

        $rows = [];
        foreach ($functions as $functionName => $info) {
            $split = preg_split('/(->|::)/', $functionName);
            if (count($split) === 1) {
                array_unshift($split, '');
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
                . "', '"
                . mysqli_real_escape_string($this->mysqli, $info['number'])
                . "')";
        }

        $sql = 'INSERT IGNORE INTO `function_call` (`file_id`, `request_id`, `class`, `function`, `type`, `calls`, `walltime`, `number`) VALUES ';
        $sql .= implode(', ', $rows);

        $this->mysqli->query($sql);
    }
}
