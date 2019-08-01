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
        $sql = 'SELECT u.`id`, u.`name`, COUNT(f.`id`) as `files`
            FROM `usecase` u
            JOIN `request` r on r.`usecase_id` = u.`id`
            JOIN `file` f ON f.`request_id` = r.`id` ' . ($file !== null ? " and f.`id` = {$file} " : '') . '
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
     * @param  int $file
     * @return array
     */
    public function getRequests($usecase = null, $application = null, $file = null): array
    {
        $sql = 'SELECT r.`id`, r.`request_id` as `name`, COUNT(f.id) as `files`
            FROM `request` r
            JOIN `application` a on r.`application_id` = a.`id` ' . ($application !== null ? " and r.`application_id` = {$application} " : '') . '
            JOIN `usecase` u     ON r.`usecase_id`     = u.`id` ' . ($usecase     !== null ? " and r.`usecase_id`     = {$usecase}     " : '') . '
            JOIN `file` f        ON f.`request_id`     = r.`id` ' . ($file        !== null ? " and f.`id`             = {$file}        " : '') . '
            WHERE 1 GROUP BY r.`id` ORDER BY `name` ASC';
        $ret = [];
        $result = $this->mysqli->query($sql);
        while ($row = $result->fetch_assoc()) {
            $ret[$row['id']] = $row;
        }
        return $ret;
    }

}
