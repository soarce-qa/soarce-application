<?php

namespace Soarce\Receiver;

use mysqli;

abstract class ReceiverAbstract
{
    protected ?int $applicationId;
    protected ?int $usecaseId = null;
    protected ?int $requestId = null;

    public function __construct(protected mysqli $mysqli)
    {}

    abstract public function persist(int $usecaseId, array $json);

    /**
     * @return int
     */
    protected function getApplicationId(): int
    {
        return $this->applicationId;
    }

    protected function createApplication(string $name): void
    {
        $escapedName = mysqli_real_escape_string($this->mysqli, $name);
        $sql = "SELECT `id` FROM `application` WHERE name='$escapedName'";
        $result = $this->mysqli->query($sql);
        if ($result->num_rows > 0) {
            $this->applicationId = $result->fetch_assoc()['id'];
            return;
        }

        $sql = 'INSERT INTO `application` SET `name` = "' . $escapedName . '"';
        $this->mysqli->query($sql);
        $this->applicationId = $this->mysqli->insert_id;
    }

    protected function createFile(string $filename, int $coverableLines = 0, string|null $md5 = null): int
    {
        $escapedFilename = mysqli_real_escape_string($this->mysqli, $filename);
        $sql = "SELECT `id` FROM `file` WHERE application_id = $this->applicationId AND filename='$escapedFilename'";
        $result = $this->mysqli->query($sql);
        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['id'];
        }

        $sql = 'INSERT INTO `file` (`application_id`, `filename`, `md5`, `lines`) VALUES ('
            . $this->getApplicationId() . ', "' . $escapedFilename
            . '", '
            . ($md5 !== null ? "0x{$md5}" : 'null')
            . ', ' . $coverableLines
            . ');';
        $this->mysqli->query($sql);

        return $this->mysqli->insert_id;
    }

    protected function getRequestId(): int
    {
        if (null === $this->requestId) {
            throw new Exception('race condition: trying to get ID of request before it was written to database');
        }
        return $this->requestId;
    }

    protected function createRequest(int $usecaseId, string $requestId, string $requestStarted, array $get, array $post, array $server, array $env): void
    {
        $escapedRequestId = mysqli_real_escape_string($this->mysqli, $requestId);
        $sql = "SELECT `id` FROM `request` WHERE request_id = '$escapedRequestId'";
        $result = $this->mysqli->query($sql);
        if ($result->num_rows > 0) {
            $this->requestId = $result->fetch_assoc()['id'];
            return;
        }

        $sql = 'INSERT IGNORE INTO `request` (`usecase_id`, `application_id`, `request_id`, `request_started`, `get`, `post`, `server`, `env`) VALUES ('
            . mysqli_real_escape_string($this->mysqli, $usecaseId)
            . ', '
            . mysqli_real_escape_string($this->mysqli, $this->getApplicationId())
            . ', "' . $escapedRequestId . '", '
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

        $this->requestId = $this->mysqli->insert_id;
    }
}
