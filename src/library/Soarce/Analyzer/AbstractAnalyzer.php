<?php

namespace Soarce\Analyzer;

use mysqli;
use Slim\Container;

abstract class AbstractAnalyzer
{
    /** @var Container */
    protected $container;

    /** @var mysqli */
    protected $mysqli;

    /**
     * Usecase constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->mysqli = $this->container->mysqli;
    }

    /**
     * @param  int   $file
     * @return array
     */
    public function getUsecases($file = null): array
    {
        $sql = 'SELECT u.`id`, u.`name`
            FROM `usecase` u
            ' . ($file !== null ? "JOIN `request` r on r.`usecase_id` = u.`id` JOIN `coverage` c ON c.`request_id` = r.`id` and c.`file_id` = {$file} " : '') . '
            WHERE 1 GROUP BY u.`id` ORDER BY u.`name` ASC';
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
     * @param  int $usecase
     * @return array
     */
    public function getAppplications($usecase = null): array
    {
        $sql = 'SELECT a.`id`, a.`name`
            FROM `application` a
            ' . ($usecase !== null ? "JOIN `request` r on r.`application_id` = a.id and r.`usecase_id` = {$usecase} " : '') . '
            WHERE 1 GROUP BY a.`id` ORDER BY a.`name` ASC';
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
     * @param  int $usecase
     * @param  int $application
     * @param  int $file
     * @return array
     */
    public function getRequests($usecase = null, $application = null, $file = null): array
    {
        $sql = 'SELECT r.`id`, r.`request_id` as `name`
            FROM `request` r
            JOIN `application` a on r.`application_id` = a.`id` ' . ($application !== null ? " and r.`application_id` = {$application} " : '')
                                                                  . ($usecase     !== null ? " and r.`usecase_id`     = {$usecase}     " : '') . '
            JOIN `coverage` c    ON c.`request_id`     = r.`id` ' . ($file        !== null ? " and c.`file_id`        = {$file}        " : '') . '
            WHERE 1 GROUP BY r.`id` ORDER BY `name` ASC';
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
}
