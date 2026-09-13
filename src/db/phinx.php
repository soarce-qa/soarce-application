<?php

$host    = getenv('MYSQL_HOST')     !== false ? getenv('MYSQL_HOST')     : 'mysql-soarce';
$name    = getenv('MYSQL_DBNAME')   !== false ? getenv('MYSQL_DBNAME')   : 'soarce';
$user    = getenv('MYSQL_USER')     !== false ? getenv('MYSQL_USER')     : 'root';
$pass    = getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : 'root';
$port    = getenv('MYSQL_PORT')     !== false ? getenv('MYSQL_PORT')     : '3306';
$charset = 'utf8';

$pdo = new PDO("mysql:host={$host};port={$port};dbname={$name};user={$user};password={$pass};options='--client_encoding={$charset}'", $user, $pass);
$res = $pdo->exec("SET NAMES '" . $charset . "'");
if ($res === false) {
    die(print_r($pdo->errorInfo(), true));
}

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/seeds',
#        'bootstrap' => '%%PHINX_CONFIG_DIR%%/phinx-bootstrap.php',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'production',
        'production' => [
            'adapter' => 'mysql',
            'connection' => $pdo,
            'name' => $name,
            'schema' => $name,
        ],
    ],
    'version_order' => 'creation'
];

