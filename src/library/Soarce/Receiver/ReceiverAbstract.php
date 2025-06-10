<?php

namespace Soarce\Receiver;

use mysqli;

abstract class ReceiverAbstract
{
    protected int $applicationId;
    protected int $usecaseId;
    protected int $requestId;

    public function __construct(protected mysqli $mysqli)
    {}

    abstract public function persist(array $json);

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
        $sql = 'INSERT INTO `application` SET `name` = "' . $escapedName . '" ON DUPLICATE KEY UPDATE `id` = LAST_INSERT_ID(`id`);';
        $this->mysqli->query($sql);
        $this->applicationId = $this->mysqli->insert_id;
    }

    protected function createFile(string $filename, int $coverableLines = 0, string|null $md5 = null): int
    {
        $escapedFilename = mysqli_real_escape_string($this->mysqli, $filename);
        $sql = 'INSERT IGNORE INTO `file` (`application_id`, `filename`, `md5`, `lines`) VALUES ('
            . $this->getApplicationId() . ', "' . $escapedFilename
            . '", '
            . ($md5 !== null ? "0x{$md5}" : 'null')
            . ', ' . (int)$coverableLines
            . ') ON DUPLICATE KEY UPDATE `id` = LAST_INSERT_ID(`id`)'
            . ($md5            !== null ? ", `md5` = 0x{$md5}"            : '')
            . ($coverableLines !== 0    ? ", `lines` = {$coverableLines}" : '')
            . ';';
        $this->mysqli->query($sql);

        return $this->mysqli->insert_id;
    }

    protected function getUsecaseId(): int
    {
        if (null === $this->usecaseId) {
            // due to the unique constraint/index there can only be one row!
            $result = $this->mysqli->query('SELECT `id` FROM `usecase` WHERE `active` = 1');
            $this->usecaseId = $result->fetch_assoc()['id'];
        }

        return $this->usecaseId;
    }

    protected function getRequestId(): int
    {
        if (null === $this->requestId) {
            throw new Exception('race condition: trying to get ID of request before it was written to database');
        }
        return $this->requestId;
    }

    protected function createRequest(string $requestId, string $requestStarted, array $get, array $post, array $server, array $env): void
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
