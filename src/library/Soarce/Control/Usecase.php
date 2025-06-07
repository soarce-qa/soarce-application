<?php

namespace Soarce\Control;

use mysqli;
use mysqli_sql_exception;

class Usecase
{
    public function __construct(protected mysqli $mysqli)
    {}

    /**
     * @return array[]
     */
    public function getAllUsecases(): array
    {
        return $this->mysqli->query('SELECT u.*, (SELECT COUNT(*) FROM `request` r WHERE r.usecase_id = u.id) as requests FROM `usecase` u WHERE 1 ORDER BY u.`id` ASC;')->fetch_all(MYSQLI_ASSOC);
    }

    public function getUsecase(int|string $usecaseNameOrId): array
    {
        return $this->mysqli->query('SELECT u.*, (SELECT COUNT(*) FROM `request` r WHERE r.usecase_id = u.id) as requests FROM `usecase` u WHERE `id` = "' . mysqli_real_escape_string($this->mysqli, $usecaseNameOrId) . '" OR `name` = "' . mysqli_real_escape_string($this->mysqli, $usecaseNameOrId) . '"')->fetch_assoc();
    }

    public function create(string $usecaseName): void
    {
        if (trim($usecaseName) === '') {
            throw new Exception('Use Case name cannot be empty');
        }
        try {
            $this->mysqli->query('INSERT INTO `usecase` (`name`) VALUES ("' . mysqli_real_escape_string($this->mysqli, $usecaseName) . '");');
            if ($this->mysqli->error) {
                die($this->mysqli->error);
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                throw new Exception('Use Case name (' . $usecaseName . ') already exists', 409);
            }
            throw $e;
        }
    }

    public function delete(int|string $usecaseNameOrId): void
    {
        if (is_numeric($usecaseNameOrId)) {
            $this->mysqli->query('DELETE FROM `usecase` WHERE `id` = "' . mysqli_real_escape_string($this->mysqli, $usecaseNameOrId) . '";');
        } else {
            $this->mysqli->query('DELETE FROM `usecase` WHERE `name` = "' . mysqli_real_escape_string($this->mysqli, $usecaseNameOrId) . '";');
        }

        if ($this->mysqli->error) {
            die($this->mysqli->error);
        }
    }

    public function activate(int|string $usecaseNameOrId): void
    {
        $this->mysqli->query('UPDATE `usecase` SET active = null WHERE 1;');
        if (is_numeric($usecaseNameOrId)) {
            $this->mysqli->query('UPDATE `usecase` SET active = 1 WHERE `id` = "' . mysqli_real_escape_string($this->mysqli, $usecaseNameOrId) . '";');
        } else {
            $this->mysqli->query('UPDATE `usecase` SET active = 1 WHERE `name` = "' . mysqli_real_escape_string($this->mysqli, $usecaseNameOrId) . '";');
        }

        if ($this->mysqli->error) {
            die($this->mysqli->error);
        }
    }

    public function restart(int|string $usecaseNameOrId): void
    {
        if (is_numeric($usecaseNameOrId)) {
            $this->mysqli->query('DELETE FROM `request` WHERE usecase_id = (SELECT id FROM `usecase` WHERE  `id` = "' . mysqli_real_escape_string($this->mysqli, $usecaseNameOrId) . '");');
        } else {
            $this->mysqli->query('DELETE FROM `request` WHERE usecase_id = (SELECT id FROM `usecase` WHERE  `name` = "' . mysqli_real_escape_string($this->mysqli, $usecaseNameOrId) . '");');
        }

        if ($this->mysqli->error) {
            die($this->mysqli->error);
        }
        $this->activate($usecaseNameOrId);
    }

    public function createOrRestart(string $usecaseNameOrId): void
    {
        try {
            $this->create($usecaseNameOrId);
        } catch (Exception $e) {
            if ($e->getCode() === 409) {
                $this->restart($usecaseNameOrId);
                return;
            }
            throw $e;
        }
    }
}
