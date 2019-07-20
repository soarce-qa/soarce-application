<?php

use Slim\App;

require_once '../../vendor/autoload.php';

$config = [
	'settings' => [
		'baseUrl'   => substr($_SERVER['SCRIPT_NAME'], 0, -9),  // "index.php" is 9 letters, we have to cut that off.
		'serverUrl' => (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on')
			? 'https://' . $_SERVER['HTTP_HOST']
			: 'http://' . $_SERVER['HTTP_HOST'],
        'soarce' => json_decode(file_get_contents(__DIR__ . '/../../soarce.json'), JSON_OBJECT_AS_ARRAY),
	],
];

$app = new App($config);

require '../application/Bootstrap.php';

$app->run();
