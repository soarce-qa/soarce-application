<?php

namespace Soarce\Analyzer;

use Soarce\Control\Service;
use Soarce\Control\Service\FileContent;

class Coverage extends AbstractAnalyzer
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

        $sql = 'SELECT a.`id` as `applicationId`, a.`name` as `applicationName`, f.`id` as `fileId`, f.`filename` as `fileName`,
                COUNT(distinct c.`line`) as `coveredLines`, f.`lines`
            FROM `file`        f
            JOIN `application` a  ON a.`id`             = f.`application_id` ' . ($applicationList !== '' ? " and a.`id` in ({$applicationList}) " : '') . '
            JOIN `request`     r  ON r.`application_id` = a.`id` ' . ($usecaseList !== '' ? " and r.`usecase_id` in ({$usecaseList}) " : '') . ($requestList !== '' ? " and r.`id` in ({$requestList}) " : '') . '
            JOIN `coverage`    c  ON c.`request_id`     = r.`id` and c.`file_id`  = f.`id` and c.`covered` = 1
            WHERE 1
            GROUP BY a.id, f.filename
            ORDER BY a.name ASC, f.filename ASC';
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
     * @param int $fileId
     * @return FileContent
     */
    public function getSource(int $fileId): FileContent
    {
        $controlService = new Service($this->container);

        $sql = 'SELECT a.`name` as `applicationName`, f.`filename` as `fileName`
            FROM `file`        f
            JOIN `application` a ON a.`id` = f.`application_id` 
            WHERE f.id = ' . (int)$fileId;

        $result = $this->mysqli->query($sql);

        if (!$result) {
            throw new AnalyzerException($this->mysqli->error, $this->mysqli->errno);
        }

        $row = $result->fetch_assoc();
        $actionable = $controlService->getServiceActionable($row['applicationName']);

        return $actionable->getFile($row['fileName']);
    }

    /**
     * @param int $fileId
     * @param int[]|null $usecaseIds
     * @param int[]|null $requestIds
     * @return int[]
     */
    public function getCoverage(int $fileId, array $usecaseIds = null, array $requestIds = null): array
    {
        $usecaseList = $this->buildInStatementBody($usecaseIds);
        $requestList = $this->buildInStatementBody($requestIds);

        $sql = 'SELECT c.`line`, MAX(c.`covered`) as `coverageLevel`
            FROM `coverage` c
            JOIN `request`  r ON r.`id` = c.`request_id` '
                . ($usecaseList !== '' ? " and r.`usecase_id` in ({$usecaseList}) " : '')
                . ($requestList !== '' ? " and r.`id` in ({$requestList}) " : '') . '
            WHERE c.`file_id` = ' . (int)$fileId . '
            GROUP BY c.`line` ';

        $result = $this->mysqli->query($sql);

        if (!$result) {
            throw new AnalyzerException($this->mysqli->error, $this->mysqli->errno);
        }

        $ret = [];
        while ($row = $result->fetch_assoc()) {
            $ret[$row['line']] = $row['coverageLevel'];
        }

        return $ret;
    }

    /**
     * @param int $fileId
     * @return array
     */
    public function getFile(int $fileId): array
    {
        $sql = 'SELECT f.`id`, f.`application_id`, f.`filename`, LOWER(HEX(f.`md5`)) as `md5`
            FROM `file` f
            WHERE f.`id` = ' . (int)$fileId;

        $result = $this->mysqli->query($sql);

        if (!$result) {
            throw new AnalyzerException($this->mysqli->error, $this->mysqli->errno);
        }

        return $result->fetch_assoc();
    }

    /**
     * @param int $fileId
     * @param int $line
     * @return array
     */
    public function getRequestsForLoc(int $fileId, int $line): array
    {
        $sql = 'SELECT DISTINCT r.`id`, r.`request_id`
            FROM `coverage` c 
            JOIN `request`  r on c.`request_id` = r.`id`
            WHERE c.`file_id` = ' . (int)$fileId . ' AND c.`line` = ' . (int)$line . ' AND c.`covered` = 1
            ORDER BY r.`request_id` ASC';

        $ret = [];
        $result = $this->mysqli->query($sql);

        if (!$result) {
            throw new AnalyzerException($this->mysqli->error, $this->mysqli->errno);
        }

        while ($row = $result->fetch_assoc()) {
            $ret[$row['id']] = $row['request_id'];
        }
        return $ret;
    }

    /**
     * @param int $fileId
     * @param int $line
     * @return array
     */
    public function getUsecasesForLoC(int $fileId, int $line): array
    {
        $sql = 'SELECT u.`id`, u.`name`
            FROM `coverage` c 
            JOIN `request`  r on c.`request_id` = r.`id`
            JOIN `usecase`  u on r.`usecase_id` = u.`id`
            WHERE c.`file_id` = ' . (int)$fileId . ' AND c.`line` = ' . (int)$line . ' AND c.`covered` = 1
            ORDER BY r.`request_id` ASC';

        $ret = [];
        $result = $this->mysqli->query($sql);

        if (!$result) {
            throw new AnalyzerException($this->mysqli->error, $this->mysqli->errno);
        }

        while ($row = $result->fetch_assoc()) {
            $ret[$row['id']] = $row['name'];
        }
        return $ret;
    }
}
