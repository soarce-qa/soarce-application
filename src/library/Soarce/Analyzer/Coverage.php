<?php

namespace Soarce\Analyzer;

class Coverage extends AbstractAnalyzer
{
    /**
     * @return array
     */
    public function getUsecases(): array
    {
        $sql = 'SELECT u.`id`, u.`name`, COUNT(f.`id`) as `files`
            FROM `usecase` u
            JOIN `request` r on r.`usecase_id` = u.`id`
            JOIN `file` f ON f.`request_id` = r.`id`
            WHERE 1 GROUP BY u.`id` ORDER BY u.`name` ASC';
        $ret = [];
        $result = $this->mysqli->query($sql);
        while ($row = $result->fetch_assoc()) {
            $ret[$row['id']] = $row;
        }
        return $ret;
    }

    /**
     * @param  int $usecase
     * @return array
     */
    public function getAppplications($usecase = null): array
    {
        $sql = 'SELECT a.`id`, a.`name`, COUNT(f.`id`) as `files`
            FROM `application` a
            JOIN `request` r on r.`application_id` = a.id ' . ($usecase !== null ? " and r.`usecase_id` = {$usecase} " : '') . '
            JOIN `usecase` u ON r.`usecase_id`     = u.`id`
            JOIN `file` f    ON f.`request_id`     = r.`id`
            WHERE 1 GROUP BY a.`id` ORDER BY u.`name` ASC';
        $ret = [];
        $result = $this->mysqli->query($sql);
        while ($row = $result->fetch_assoc()) {
            $ret[$row['id']] = $row;
        }
        return $ret;
    }

    /**
     * @param  int $usecase
     * @param  int $application
     * @return array
     */
    public function getRequests($usecase = null, $application = null): array
    {
        $sql = 'SELECT r.`id`, r.`request_id` as `name`, COUNT(f.id) as `files`
            FROM `request` r
            JOIN `application` a on r.`application_id` = a.`id` ' . ($application !== null ? " and r.`application_id` = {$application} " : '') . '
            JOIN `usecase` u     ON r.`usecase_id`     = u.`id` ' . ($usecase     !== null ? " and r.`usecase_id`     = {$usecase}     " : '') . '
            JOIN `file` f        ON f.`request_id`     = r.`id`
            WHERE 1 GROUP BY r.`id` ORDER BY `name` ASC';
        $ret = [];
        $result = $this->mysqli->query($sql);
        while ($row = $result->fetch_assoc()) {
            $ret[$row['id']] = $row;
        }
        return $ret;
    }

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
}
