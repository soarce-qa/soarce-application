<?php

namespace Soarce\Analyzer;

class Request extends AbstractAnalyzer
{
    public function getRequestsOverview($usecases = [], $applications = []): array
    {
        $applicationList = $this->buildInStatementBody($applications);
        $usecaseList     = $this->buildInStatementBody($usecases);


        $sql = 'SELECT r.`id`, r.`request_id`, FROM_UNIXTIME(r.`request_started`) AS `request_started`, u.`name` as `usecaseName`, a.`name` as `applicationName`
            FROM `request` r
            JOIN `application` a ON r.`application_id` = a.`id`
            JOIN `usecase` u     ON r.`usecase_id`     = u.`id`
            WHERE 1 '
            . ($applicationList !== '' ? " and r.`application_id` in ({$applicationList}) " : '')
            . ($usecaseList     !== '' ? " and r.`usecase_id`     in ({$usecaseList}) "     : '') . '
            ORDER BY r.`request_started` ASC';
        $result = $this->mysqli->query($sql);

        if (!$result) {
            throw new AnalyzerException($this->mysqli->error, $this->mysqli->errno);
        }

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }
}
