<?php

namespace Soarce\Maintenance;

use mysqli;
use Slim\Container;

class Database
{
    /** @var Container */
    private $container;

    /** @var mysqli */
    private $mysqli;

    /**
     * Database constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->mysqli = $this->container->mysqli;
    }

    public function purgeAll(): void
    {
        foreach (['usecases', 'application'] as $table) {
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
