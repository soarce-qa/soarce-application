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
        $sql = 'INSERT IGNORE INTO `application` SET `name` = "' . $escapedName . '"';

        $this->mysqli->query($sql);

        if (0 === ($id = $this->mysqli->insert_id)) {
            $result = $this->mysqli->query('SELECT `id` FROM `application` WHERE `name` = "' . $escapedName . '"');
            $id = $result->fetch_assoc()['id'];
        }
        $this->applicationId = $id;
    }

    /**
     * @param  string $filename
     * @return int
     */
    protected function createFile($filename): int
    {
        $escapedFilename = mysqli_real_escape_string($this->mysqli, $filename);
        $sql = 'INSERT IGNORE INTO `files` (`application_id`, `request_id`, `filename`) VALUES (
            ' . $this->getApplicationId() . ', ' . $this->getRequestId() . ', "' . $escapedFilename . '");';
        $this->mysqli->query($sql);

        if (0 === ($id = $this->mysqli->insert_id)) {
            $result = $this->mysqli->query('SELECT `id` FROM `files` WHERE `filename` = "' . $escapedFilename . '" AND application_id = ' . $this->getApplicationId());
            $id = $result->fetch_assoc()['id'];
        }
        return $id;
    }

    /**
     * @return int
     */
    protected function getUsecaseId(): int
    {
        if (null === $this->usecaseId) {
            // due to the unique constraint/index there can only be one row!
            $result = $this->mysqli->query('SELECT `id` FROM `usecases` WHERE `active` = 1');
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
        $sql = 'INSERT IGNORE INTO `requests` (`usecase_id`, `application_id`, `request_id`, `request_started`, `get`, `post`, `server`, `env`) VALUES ('
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
            . '");';

        $this->mysqli->query($sql);

        if (0 === ($id = $this->mysqli->insert_id)) {
            $result = $this->mysqli->query('SELECT `id` FROM `requests` WHERE `request_id` = "' . mysqli_real_escape_string($this->mysqli, $requestId) . '"');
            $id = $result->fetch_assoc()['id'];
        }
        $this->requestId = $id;
    }


}
