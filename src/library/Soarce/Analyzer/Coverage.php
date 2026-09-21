<?php

namespace Soarce\Analyzer;

use mysqli;
use Soarce\Control\Service;
use Soarce\Control\Service\FileContent;
use Soarce\DataTables\ServersidePaginatorFilter;

class Coverage extends AbstractAnalyzer
{
    private const COLUMN_MAP = [
        'file' => 'fileName',
        'lines' => 'lines',
        'totalCoveredLines' => 'totalCoverage',
        'totalPercentageNumber' => 'totalCoveragePercent',
    ];

    public function __construct(mysqli $mysqli, private Service $service)
    {
        parent::__construct($mysqli);
    }

    public function getTotalFileCount(): int
    {
        $sql = "SELECT count(*) as `totalFileCount` FROM `file`";
        $result = $this->mysqli->query($sql);
        $row = $result->fetch_assoc();
        return $row['totalFileCount'];
    }

    public function getFilteredFileCount(array $applications = [], array $usecases = [], array $requests = [], ?ServersidePaginatorFilter $serversidePaginatorFilter = null): int
    {
        $query = $this->buildQuery($applications, $usecases, $requests, $serversidePaginatorFilter);
        $query = str_replace('%%%FIELDS%%%', 'count(*) as `filteredFileCount`', $query);
        $result = $this->mysqli->query($query);
        $row = $result->fetch_assoc();
        return $row['filteredFileCount'];
    }

    private function buildQuery(array $applications = [], array $usecases = [], array $requests = [], ?ServersidePaginatorFilter $serversidePaginatorFilter = null): string
    {
        $sql = "SELECT %%%FIELDS%%% FROM `file` f ";
        $where = '1 ';

        if (count($applications) > 0) {
            $applicationList = $this->buildInStatementBody($applications);
            $where .= " AND f.`application_id` IN ($applicationList) ";
        }

        if (count($usecases) > 0 && count($requests) === 0) {
            $requests = $this->getRequestIdsFromUsecases($usecases);
        }

        if (count($requests) > 0) {
            $requestList = $this->buildInStatementBody($requests);
            $subSql2 = "SELECT DISTINCT c.`file_id` from `coverage` c where c.`request_id` IN ($requestList)";
            $result = $this->mysqli->query($subSql2)->fetch_all(MYSQLI_ASSOC);
            $fileIds = array_column($result, 'file_id');
            $fileIdList = $this->buildInStatementBody($fileIds);
            $where .= " AND f.`id` IN ($fileIdList)";
        }

        if ($serversidePaginatorFilter->getSearch()->getValue() !== '') {
            $where .= " AND f.`filename` LIKE '%" . $serversidePaginatorFilter->getSearch()->getValue() . "%'";
        }
        
        return $sql . ' WHERE ' . $where;
    }

    public function getFiles(array $applications = [], array $usecases = [], array $requests = [], ?ServersidePaginatorFilter $serversidePaginatorFilter = null): array
    {
        $query = $this->buildQuery($applications, $usecases, $requests, $serversidePaginatorFilter);
        $query = str_replace('%%%FIELDS%%%', 'f.`application_id` as `applicationId`, f.`id` as `fileId`, f.`filename` as `fileName`, f.`lines` as `lines`, f.`total_coverage` as `totalCoverage`, f.`total_coverage_percent` as `totalCoveragePercent`', $query);

        if ($serversidePaginatorFilter instanceof ServersidePaginatorFilter) {
            $query .= " ORDER BY " . $serversidePaginatorFilter->getOrderSQL(self::COLUMN_MAP)
                . " LIMIT " . min($serversidePaginatorFilter->getLength(), 1000)
                . " OFFSET " . $serversidePaginatorFilter->getStart();
        }

        $result = $this->mysqli->query($query);

        if (!$result) {
            throw new AnalyzerException($this->mysqli->error, $this->mysqli->errno);
        }


        if ($result->num_rows > 0) {
            $applicationsLookup = $this->getAppplications();
            $res = $result->fetch_all(MYSQLI_ASSOC);
            $coverageForFiles = $this->filteredCoverageForFiles(
                array_column($res, 'fileId'),
                $usecases,
                $requests
            );
            foreach ($res as &$row) {
                $row['applicationName'] = $applicationsLookup[$row['applicationId']]['name'];
                $row['filteredCoveredLines'] = $coverageForFiles[$row['fileId']];
                $row['filteredCoveredPercent'] = number_format(($coverageForFiles[$row['fileId']] / $row['lines']) * 100, 2);
            }
            return $res;
        }
        return [];
    }

    public function filteredCoverageForFiles(array $fileIds = [], array $usecases = [], array $requests = []): array
    {
        if (count($usecases) > 0 && count($requests) === 0) {
            $requests = $this->getRequestIdsFromUsecases($usecases);
        }

        $fileList    = $this->buildInStatementBody($fileIds);

        $sql = 'SELECT c.`file_id` as `fileId`, COUNT(distinct c.`line`) as `filteredCoveredLines`
            FROM `coverage` c
            WHERE c.`file_id` IN (' . $fileList . ') and c.`covered` = 1';

        if (count($requests) > 0) {
            $requestList = $this->buildInStatementBody($requests);
            $sql .= ' and c.`request_id` IN (' . $requestList . ') ';
        }

        $sql .= ' GROUP BY c.`file_id`';

        $result = $this->mysqli->query($sql);

        if (!$result) {
            throw new AnalyzerException($this->mysqli->error, $this->mysqli->errno);
        }

        if ($result->num_rows > 0) {
            return array_column($result->fetch_all(MYSQLI_ASSOC), 'filteredCoveredLines', 'fileId');
        }
        return [];
    }
    /**
     * @param int $fileId
     * @return FileContent
     */
    public function getSource(int $fileId): FileContent
    {
        $sql = 'SELECT a.`name` as `applicationName`, f.`filename` as `fileName`
            FROM `file`        f
            JOIN `application` a ON a.`id` = f.`application_id` 
            WHERE f.id = ' . (int)$fileId;

        $result = $this->mysqli->query($sql);

        if (!$result) {
            throw new AnalyzerException($this->mysqli->error, $this->mysqli->errno);
        }

        $row = $result->fetch_assoc();
        return $this->service->getServiceActionable($row['applicationName'])->getFile($row['fileName']);
    }

    /**
     * @param int $fileId
     * @param int[]|null $usecaseIds
     * @param int[]|null $requestIds
     * @return int[]
     */
    public function getCoverage(int $fileId, ?array $usecaseIds, ?array $requestIds): array
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

        return array_column($result->fetch_all(MYSQLI_ASSOC), 'coverageLevel', 'line');
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

        $result = $this->mysqli->query($sql);

        if (!$result) {
            throw new AnalyzerException($this->mysqli->error, $this->mysqli->errno);
        }

        return array_column($result->fetch_all(MYSQLI_ASSOC), 'request_id', 'id');
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

        $result = $this->mysqli->query($sql);

        if (!$result) {
            throw new AnalyzerException($this->mysqli->error, $this->mysqli->errno);
        }

        return array_column($result->fetch_all(MYSQLI_ASSOC), 'name', 'id');
    }

    public function getTotalCoveragePercentage(): float
    {
        $sql = 'SELECT SUM(f.lines) as `total_lines`, SUM(f.total_coverage) as `total_covered` FROM file f WHERE 1';

        $result = $this->mysqli->query($sql)->fetch_assoc();

        if ((int)$result['total_lines'] === 0) {
            return 0.0;
        }
        return $result['total_covered'] / $result['total_lines'];
    }

    public function getApplicationStats(): array
    {
        $applicationsLookup = $this->getAppplications();

        $sql = 'SELECT
            f.application_id,
            SUM(f.lines) as total_lines,
            count(*)     as `files`,
            SUM(f.total_coverage) as total_covered
            from file f
            where true
            group by f.application_id';

        $ret = $this->mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);

        foreach ($ret as &$row) {
            $row['name'] = $applicationsLookup[$row['application_id']]['name'];
        }

        return $ret;
    }
    private function getRequestIdsFromUsecases(array $usecases): array
    {
        $usecaseList = $this->buildInStatementBody($usecases);
        $subSql = "SELECT DISTINCT r.`id` from `request` r where r.`usecase_id` IN ($usecaseList)";
        $result = $this->mysqli->query($subSql)->fetch_all(MYSQLI_ASSOC);
        return array_column($result, 'id');
    }
}
