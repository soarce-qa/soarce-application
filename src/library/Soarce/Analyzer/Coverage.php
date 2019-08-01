<?php

namespace Soarce\Analyzer;

use Soarce\Control\Service;

class Coverage extends AbstractAnalyzer
{

    /**
     * @param  int   $application
     * @param  int   $usecase
     * @param  int   $request
     * @return array
     */
    public function getFiles($application = null, $usecase = null, $request = null): array
    {
        $sql = 'SELECT a.id as applicationId, a.name as applicationName, f.id as fileId, f.filename as fileName, COUNT(distinct c.line) as `lines`
            FROM `file`        f
            JOIN `coverage`    c ON c.`file_id` = f.`id`
            JOIN `request`     r ON r.`id`      = f.`request_id` '     . ($usecase     !== null ? " and r.`usecase_id` = {$usecase} "     : '')
                                                                       . ($request     !== null ? " and r.`id`         = {$request} "     : '') . '
            JOIN `application` a ON a.`id`      = r.`application_id` ' . ($application !== null ? " and a.`id`         = {$application} " : '') . '
            WHERE 1
            GROUP BY a.name, f.filename
            ORDER BY a.name ASC, f.filename ASC';
        $result = $this->mysqli->query($sql);

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    /**
     * @param  int $fileId
     * @return string[]
     */
    public function getSource($fileId): array
    {
        $controlService = new Service($this->container);

        $sql = 'SELECT a.`name` as `applicationName`, f.`filename` as `fileName`
            FROM `file`        f
            JOIN `request`     r ON r.`id`      = f.`request_id`
            JOIN `application` a ON a.`id`      = r.`application_id` 
            WHERE f.id = ' . (int)$fileId;

        $row = $this->mysqli->query($sql)->fetch_assoc();

        $actionable = $controlService->getServiceActionable($row['applicationName']);
        return explode("\n", $actionable->getFile($row['fileName']));
    }

    /**
     * @param  int $fileId
     * @param  int $usecaseId
     * @param  int $requestId
     * @return int[]
     */
    public function getCoverage($fileId, $usecaseId = null, $requestId = null): array
    {
        $sql = 'SELECT c.`line`
            FROM `coverage`    c
            JOIN `file`        f ON c.`file_id` = f.`id` AND f.`id` = ' . (int)$fileId . '
            JOIN `request`     r ON r.`id`      = f.`request_id`'       . ($usecaseId     !== null ? " and r.`usecase_id` = {$usecaseId} "     : '')
                                                                        . ($requestId     !== null ? " and r.`id`         = {$requestId} "     : '') . '
            WHERE 1';

        $result = $this->mysqli->query($sql);

        $ret = [];
        while ($row = $result->fetch_assoc()) {
            $ret[$row['line']] = true;
        }

        return $ret;
    }

    /**
     * @param  int   $fileId
     * @param  int   $line
     * @return array
     */
    public function getRequestsForLoc($fileId, $line): array
    {
        $sql = 'SELECT DISTINCT r.`id`, r.`request_id`
            FROM `coverage` c 
            JOIN `file`     f ON c.`file_id`    = f.`id`
            JOIN `request`  r on f.`request_id` = r.`id`
            WHERE c.`file_id` = ' . (int)$fileId . ' AND c.`line` = ' . (int)$line . '
            ORDER BY r.`request_id` ASC';

        $ret = [];
        $result = $this->mysqli->query($sql);
        while ($row = $result->fetch_assoc()) {
            $ret[$row['id']] = $row['request_id'];
        }
        return $ret;
    }

    /**
     * @param  int   $fileId
     * @param  int   $line
     * @return array
     */
    public function getUsecasesForLoC($fileId, $line): array
    {
        $sql = 'SELECT u.`id`, u.`name`
            FROM `coverage` c 
            JOIN `file`     f ON c.`file_id`    = f.`id`
            JOIN `request`  r on f.`request_id` = r.`id`
            JOIN `usecase`  u on r.`usecase_id` = u.`id`
            WHERE c.`file_id` = ' . (int)$fileId . ' AND c.`line` = ' . (int)$line . '
            ORDER BY r.`request_id` ASC';

        $ret = [];
        $result = $this->mysqli->query($sql);
        while ($row = $result->fetch_assoc()) {
            $ret[$row['id']] = $row['name'];
        }
        return $ret;
    }
}
