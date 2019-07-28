<?php

namespace Soarce\Analyzer;

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
                                                                       . ($request     !== null ? " and r.`request_id` = {$request} "     : '') . '
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
