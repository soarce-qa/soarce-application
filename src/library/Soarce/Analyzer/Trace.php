<?php

namespace Soarce\Analyzer;

class Trace extends AbstractAnalyzer
{
    /**
     * @param int[] $applications
     * @param int[] $usecases
     * @param int[] $requests
     * @return array
     */
    public function getFiles(array $applications = [], array $usecases = [], array $requests = []): array
    {
        $applicationList = $this->buildInStatementBody($applications);
        $usecaseList     = $this->buildInStatementBody($usecases);
        $requestList     = $this->buildInStatementBody($requests);

        $sql = 'SELECT any_value(f.id) as `id`, f.filename as `name`, COUNT(distinct c.id) as `calls`
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

        return array_column($result->fetch_all(MYSQLI_ASSOC), null, 'id');
    }

    /**
     * @param int[] $applications
     * @param int[] $usecases
     * @param int[] $requests
     * @param int[] $files
     * @return array
     */
    public function getFunctionCalls(array $applications, array $usecases, array $requests, array $files): array
    {
        $applicationList = $this->buildInStatementBody($applications);
        $usecaseList     = $this->buildInStatementBody($usecases);
        $requestList     = $this->buildInStatementBody($requests);
        $fileList        = $this->buildInStatementBody($files);

        $sql = 'SELECT any_value(c.`id`) as `id`, c.`class`, c.`function`, any_value(c.`type`) as `type`, sum(c.`calls`) as `calls`, sum(c.`walltime`) as `walltime`
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
     * @param string $class
     * @param string $function
     * @param int[] $applications
     * @param int[] $usecases
     * @param int[] $requests
     * @param int[] $files
     * @return array[]
     */
    public function getCallees(string $class, string $function, array $applications, array $usecases, array $requests, array $files): array
    {
        $sql = 'SELECT b.class, b.`function`, SUM(m.calls) numcalls
            FROM `function_map` m
            JOIN `function_call` b ON m.callee = b.id
            WHERE m.caller IN (' . implode(',', $this->getFunctionIds($class, $function, $applications, $usecases, $requests, $files)) . ')
            GROUP BY b.class, b.`function`
            ORDER BY numcalls desc, b.`class` asc, b.`function` asc';

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
     * @param string $class
     * @param string $function
     * @param int[] $applications
     * @param int[] $usecases
     * @param int[] $requests
     * @param int[] $files
     * @return array[]
     */
    public function getCallers(string $class, string $function, array $applications, array $usecases, array $requests, array $files): array
    {
        $sql = 'SELECT b.class, b.`function`, SUM(m.calls) numcalls
            FROM `function_map` m
            JOIN `function_call` b ON m.caller = b.id
            WHERE m.callee IN (' . implode(', ', $this->getFunctionIds($class, $function, $applications, $usecases, $requests, $files)) . ')
            GROUP BY b.class, b.`function`
            ORDER BY numcalls desc, b.`class` asc, b.`function` asc';

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
     * @param string $class
     * @param string $function
     * @param int[] $applications
     * @param int[] $usecases
     * @param int[] $requests
     * @param int[] $files
     * @return int[]
     */
    private function getFunctionIds(string $class, string $function, array $applications, array $usecases, array $requests, array $files): array
    {
        $applicationList = $this->buildInStatementBody($applications);
        $usecaseList     = $this->buildInStatementBody($usecases);
        $requestList     = $this->buildInStatementBody($requests);
        $fileList        = $this->buildInStatementBody($files);
        $class           = mysqli_real_escape_string($this->mysqli, $class);
        $function        = mysqli_real_escape_string($this->mysqli, $function);

        $sql = 'SELECT c.`id`
            FROM `function_call` c
            JOIN `file`          f ON c.`file_id` = f.`id` '             . ($fileList        !== '' ? " and f.`id`             in ({$fileList}) "        : '') . '
            JOIN `request`       r ON r.`id`      = c.`request_id` '     . ($usecaseList     !== '' ? " and r.`usecase_id`     in ({$usecaseList}) "     : '')
                                                                         . ($requestList     !== '' ? " and r.`id`             in ({$requestList}) "     : '')
                                                                         . ($applicationList !== '' ? " and r.`application_id` in ({$applicationList}) " : '') . '
            WHERE c.`class` = "' . $class . '" AND c.`function` = "' . $function . '"';
        $result = $this->mysqli->query($sql);

        if (!$result) {
            throw new AnalyzerException($this->mysqli->error, $this->mysqli->errno);
        }

        if ($result->num_rows <= 0) {
            return [];
        }
        $res = $result->fetch_all(MYSQLI_ASSOC);
        return array_column($res, 'id');
    }

    /**
     * @param int[] $application
     * @param int[] $file
     * @return array
     */
    public function getFunctionCallsForSelect(array $application, array $file): array
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
