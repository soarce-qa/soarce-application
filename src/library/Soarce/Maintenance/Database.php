<?php

namespace Soarce\Maintenance;

use mysqli;

class Database
{
    public function __construct(private mysqli $mysqli)
    {}

    public function purgeAll(): void
    {
        foreach (['usecase', 'application'] as $table) {
            echo $sql = "DELETE FROM `{$table}` WHERE 1;";
            $this->mysqli->query($sql);
            if ($this->mysqli->error) {
                die ($this->mysqli->error);
            }
        }
    }

    public function resetAutoIncrement(): void
    {
        foreach ($this->getAllTableNames() as $table) {
            echo $sql = "ALTER TABLE `{$table['TABLE_NAME']}` AUTO_INCREMENT=1;";
            $this->mysqli->query($sql);
        }
    }

    /**
     * @return string[]
     */
    public function getAllTableNames(): array
    {
        return $this->mysqli->query('SELECT t.`TABLE_NAME` FROM information_schema.`TABLES` t WHERE t.TABLE_SCHEMA = "soarce";')->fetch_all(MYSQLI_ASSOC);
    }
}
