<?php

namespace Soarce\Control;

use mysqli;

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

    /**
     * @param string $usecaseName
     */
    public function create($usecaseName): void
    {
        $this->mysqli->query('INSERT INTO `usecase` (`name`) VALUES ("' . mysqli_real_escape_string($this->mysqli, $usecaseName) . '");');
        if ($this->mysqli->error) {
            die($this->mysqli->error);
        }
    }

    /**
     * @param string|int $usecaseNameOrId
     */
    public function delete($usecaseNameOrId): void
    {
        $this->mysqli->query('DELETE FROM `usecase` WHERE `id` = "' . mysqli_real_escape_string($this->mysqli, $usecaseNameOrId) . '" OR `name` = "' . mysqli_real_escape_string($this->mysqli, $usecaseNameOrId) . '";');
        if ($this->mysqli->error) {
            die($this->mysqli->error);
        }
    }

    /**
     * @param string|int $usecaseNameOrId
     */
    public function activate($usecaseNameOrId): void
    {
        $this->mysqli->query('UPDATE `usecase` SET active = null WHERE 1;');
        $this->mysqli->query('UPDATE `usecase` SET active = 1 WHERE `id` = "' . mysqli_real_escape_string($this->mysqli, $usecaseNameOrId) . '" OR `name` = "' . mysqli_real_escape_string($this->mysqli, $usecaseNameOrId) . '";');

        if ($this->mysqli->error) {
            die($this->mysqli->error);
        }
    }

    /**
     * @param string|int $usecaseNameOrId
     */
    public function restart($usecaseNameOrId): void
    {
        $this->mysqli->query('DELETE FROM `request` WHERE usecase_id = (SELECT id FROM `usecase` WHERE  `id` = "' . mysqli_real_escape_string($this->mysqli, $usecaseNameOrId) . '" OR `name` = "' . mysqli_real_escape_string($this->mysqli, $usecaseNameOrId) . '");');

        if ($this->mysqli->error) {
            die($this->mysqli->error);
        }
        $this->activate($usecaseNameOrId);
    }
}
