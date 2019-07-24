<?php

namespace Soarce\Statistics;

use mysqli;
use Slim\Container;

class Database
{
    const MAX_TINYINT   =                  255;
    const MAX_SMALLINT  =                65535;
    const MAX_MEDIUMINT =             16777215;
    const MAX_INT       =           4294967295;
    const MAX_BIGINT    = 18446744073709551615;

    /** @var Container */
    private $container;

    /** @var mysqli */
    private $mysqli;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->mysqli = $this->container->mysqli;
    }

    /**
     * @return array
     */
    public function getMysqlStats(): array
    {
        $tables = $this->mysqli->query('SELECT t.`TABLE_NAME`, t.TABLE_ROWS, t.DATA_LENGTH, t.INDEX_LENGTH, t.`AUTO_INCREMENT` FROM information_schema.`TABLES` t WHERE t.TABLE_SCHEMA = "soarce";')->fetch_all(MYSQLI_ASSOC);
        foreach ($tables as &$table) {
            $temp = $this->mysqli->query('SELECT count(*) as `TABLE_ROWS` FROM ' . $table['TABLE_NAME'] . ';')->fetch_assoc();
            $table['TABLE_ROWS'] = $temp['TABLE_ROWS'];

            $temp = $this->mysqli->query('SELECT c.DATA_TYPE FROM information_schema.`COLUMNS` c WHERE c.`TABLE_SCHEMA` = "soarce" AND c.`TABLE_NAME` = "' . $table['TABLE_NAME'] . '" AND c.`COLUMN_NAME`="id";')->fetch_assoc();
            $table['INDEX_PERCENTAGE'] = $table['AUTO_INCREMENT'] / constant('self::MAX_' . strtoupper($temp['DATA_TYPE']));
        }

        return $tables;
    }
}
