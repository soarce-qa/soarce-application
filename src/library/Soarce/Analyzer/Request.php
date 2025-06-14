<?php

namespace Soarce\Analyzer;

class Request extends AbstractAnalyzer
{
    public function getRequestsOverview(array $usecases = [], array $applications = []): array
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

    /**
     * @param int $requestId
     * @return array
     */
    public function getRequest(int $requestId): array
    {
        $sql = 'SELECT r.`id`, r.`request_id`, FROM_UNIXTIME(r.`request_started`) AS `request_started`, r.`get`, r.`post`, r.`server`, r.`env`,
                u.`name` as `usecaseName`, a.`name` as `applicationName`
            FROM `request` r
            JOIN `application` a ON r.`application_id` = a.`id`
            JOIN `usecase` u     ON r.`usecase_id`     = u.`id`
            WHERE r.`id` = ' . (int)$requestId;

        $result = $this->mysqli->query($sql);

        if (!$result) {
            throw new AnalyzerException($this->mysqli->error, $this->mysqli->errno);
        }

        $temp = $result->fetch_assoc();
        $temp['get']    = json_decode($temp['get'],    JSON_OBJECT_AS_ARRAY);
        $temp['post']   = json_decode($temp['post'],   JSON_OBJECT_AS_ARRAY);
        $temp['server'] = json_decode($temp['server'], JSON_OBJECT_AS_ARRAY);
        $temp['env']    = json_decode($temp['env'],    JSON_OBJECT_AS_ARRAY);

        return $temp;
    }

    /**
     * @param string $requestId
     * @return array[]
     */
    public function getSequence(string $requestId): array
    {
        $sql = 'SELECT r.`id`, r.`request_id`, FROM_UNIXTIME(r.`request_started`) AS `request_started`, a.`name` as `applicationName`, a.`id` as `applicationId`
            FROM `request` r
            JOIN `application` a ON r.`application_id` = a.`id`
            WHERE r.`request_id` LIKE "' . $requestId . '%"
            ORDER BY r.`request_id`';

        $result = $this->mysqli->query($sql);

        if (!$result) {
            throw new AnalyzerException($this->mysqli->error, $this->mysqli->errno);
        }

        return array_column($result->fetch_all(MYSQLI_ASSOC), null, 'request_id');
    }


}
