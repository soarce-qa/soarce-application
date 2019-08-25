<?php

namespace Soarce\Analyzer;

class Trace extends AbstractAnalyzer
{
    /**
     * @param  int[]   $applications
     * @param  int[]   $usecases
     * @param  int[]   $requests
     * @return array
     */
    public function getFiles($applications = [], $usecases = [], $requests = []): array
    {
        $applicationList = $this->buildInStatementBody($applications);
        $usecaseList     = $this->buildInStatementBody($usecases);
        $requestList     = $this->buildInStatementBody($requests);

        $sql = 'SELECT f.id, f.filename as `name`, COUNT(distinct c.id) as `calls`
            FROM `file`          f
            JOIN `function_call` c ON c.`file_id` = f.`id`
            JOIN `request`       r ON r.`id`      = c.`request_id` '     . ($usecaseList     !== '' ? " and r.`usecase_id`     in ({$usecaseList}) "     : '')
                                                                         . ($requestList     !== '' ? " and r.`id`             in ({$requestList}) "     : '')
                                                                         . ($applicationList !== '' ? " and r.`application_id` in ({$applicationList}) " : '') . '
            WHERE 1
            GROUP BY f.filename
            ORDER BY f.filename ASC';
        $ret = [];
        $result = $this->mysqli->query($sql);

        if (!$result) {
            throw new AnalyzerException($this->mysqli->error, $this->mysqli->errno);
        }

        while ($row = $result->fetch_assoc()) {
            $ret[$row['id']] = $row;
        }
        return $ret;
    }

    /**
     * @param  int[] $applications
     * @param  int[] $usecases
     * @param  int[] $requests
     * @param  int[] $files
     * @return array
     */
    public function getFunctionCalls($applications, $usecases, $requests, $files): array
    {
        $applicationList = $this->buildInStatementBody($applications);
        $usecaseList     = $this->buildInStatementBody($usecases);
        $requestList     = $this->buildInStatementBody($requests);
        $fileList        = $this->buildInStatementBody($files);

        $sql = 'SELECT c.`id`, c.`class`, c.`function`, c.`type`, sum(c.`calls`) as `calls`, sum(c.`walltime`) as `walltime`
            FROM `function_call` c
            JOIN `file`          f ON c.`file_id` = f.`id` '             . ($fileList        !== '' ? " and f.`id`             in ({$fileList}) "        : '') . '
            JOIN `request`       r ON r.`id`      = c.`request_id` '     . ($usecaseList     !== '' ? " and r.`usecase_id`     in ({$usecaseList}) "     : '')
                                                                         . ($requestList     !== '' ? " and r.`id`             in ({$requestList}) "     : '')
                                                                         . ($applicationList !== '' ? " and r.`application_id` in ({$applicationList}) " : '') . '
            WHERE 1
            GROUP BY c.class, c.function
            ORDER BY c.class ASC, c.function ASC';
        $result = $this->mysqli->query($sql);

        if (!$result) {
            throw new AnalyzerException($this->mysqli->error, $this->mysqli->errno);
        }

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    /**
     * @param  int[] $application
     * @param  int[] $file
     * @return array
     */
    public function getFunctionCallsForSelect($application, $file): array
    {
        $ret = [];
        foreach ($this->getFunctionCalls($application, [], [], $file) as $id => $row) {
            $ret[$row['id']] = [
                'id'   => $row['id'],
                'name' => "{$row['class']} - {$row['function']}",
            ];
        }
        return $ret;
    }
}
