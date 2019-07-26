<?php

return [
    'settings' => [
        'displayErrorDetails' => true,

        'baseUrl'   => substr($_SERVER['SCRIPT_NAME'], 0, -9),  // "index.php" is 9 letters, we have to cut that off.
        'serverUrl' => (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on') ? 'https://' . $_SERVER['HTTP_HOST'] : 'http://' . $_SERVER['HTTP_HOST'],

        'database' => [
            'host'     => 'mysql-soarce',
            'user'     => 'root',
            'password' => 'root',
            'database' => 'soarce',
        ],

        'soarce' => [
            'services' => \Soarce\Config::load(__DIR__ . '/../soarce.json'),
        ],
    ],
];
