<?php

namespace Soarce\Receiver;

use mysqli;
use Slim\Container;

abstract class ReceiverAbstract
{
    /** @var Container */
    protected $container;

    /** @var mysqli */
    protected $mysqli;

    /** @var int */
    protected $applicationId;

    /** @var int */
    protected $usecaseId;

    /** @var int */
    protected $requestId;

    /**
     * CoverageReceiver constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->mysqli = $this->container->mysqli;
    }

    /**
     * @param array $coverage
     */
    abstract public function persist($coverage);

    /**
     * @return int
     */
    protected function getApplicationId(): int
    {
        return $this->applicationId;
    }

    /**
     * @param string $name
     */
    protected function createApplication($name): void
    {
        $escapedName = mysqli_real_escape_string($this->mysqli, $name);
        $sql = 'INSERT INTO `application` SET `name` = "' . $escapedName . '" ON DUPLICATE KEY UPDATE `id` = LAST_INSERT_ID(`id`);';
        $this->mysqli->query($sql);
        $this->applicationId = $this->mysqli->insert_id;
    }

    /**
     * @param  string $filename
     * @param  string $md5
     * @return int
     */
    protected function createFile($filename, $md5 = null): int
    {
        $escapedFilename = mysqli_real_escape_string($this->mysqli, $filename);
        $sql = 'INSERT IGNORE INTO `file` (`request_id`, `filename`, `md5`) VALUES ('
            . $this->getRequestId() . ', "' . $escapedFilename
            . '", '
            . ($md5 !== null ? "0x{$md5}" : 'null')
            . ') ON DUPLICATE KEY UPDATE `id` = LAST_INSERT_ID(`id`)'
            . ($md5 !== null ? ", `md5` = 0x{$md5}" : '')
            . ';';
        $this->mysqli->query($sql);

        return $this->mysqli->insert_id;
    }

    /**
     * @return int
     */
    protected function getUsecaseId(): int
    {
        if (null === $this->usecaseId) {
            // due to the unique constraint/index there can only be one row!
            $result = $this->mysqli->query('SELECT `id` FROM `usecase` WHERE `active` = 1');
            $this->usecaseId = $result->fetch_assoc()['id'];
        }

        return $this->usecaseId;
    }

    /**
     * @return int
     */
    protected function getRequestId(): int
    {
        return $this->requestId;
    }

    /**
     * @param string $requestId
     * @param string $requestStarted
     * @param array  $get
     * @param array  $post
     * @param array  $server
     * @param array  $env
     */
    protected function createRequest($requestId, $requestStarted, $get, $post, $server, $env): void
    {
        $sql = 'INSERT IGNORE INTO `request` (`usecase_id`, `application_id`, `request_id`, `request_started`, `get`, `post`, `server`, `env`) VALUES ('
            . mysqli_real_escape_string($this->mysqli, $this->getUsecaseId())
            . ', '
            . mysqli_real_escape_string($this->mysqli, $this->getApplicationId())
            . ', "'
            . mysqli_real_escape_string($this->mysqli, $requestId)
            . '", '
            . mysqli_real_escape_string($this->mysqli, $requestStarted)
            . ', "'
            . mysqli_real_escape_string($this->mysqli, json_encode($get, JSON_PRETTY_PRINT))
            . '", "'
            . mysqli_real_escape_string($this->mysqli, json_encode($post, JSON_PRETTY_PRINT))
            . '", "'
            . mysqli_real_escape_string($this->mysqli, json_encode($server, JSON_PRETTY_PRINT))
            . '", "'
            . mysqli_real_escape_string($this->mysqli, json_encode($env, JSON_PRETTY_PRINT))
            . '") ON DUPLICATE KEY UPDATE `id` = LAST_INSERT_ID(`id`);';

        $this->mysqli->query($sql);

        $this->requestId = $this->mysqli->insert_id;
    }


}
