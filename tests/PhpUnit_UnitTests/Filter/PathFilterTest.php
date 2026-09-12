<?php

namespace UnitTests;

use Soarce\Config;
use Soarce\Filter\PathFilter;

class PathFilterTest extends \PHPUnit\Framework\TestCase{
    private const CONFIG = <<<JSON
{
  "services": {
    "testService": {
      "url": "http://test-service.example.com/",
      "parameter_name": "SOARCE",
      "common_path": "/var/www/html",
      "preshared_secret": "yo-mama!",
      "filters": {
        "allow": [
          "/var/www/html/src/application/",
          "/var/www/html/vendor/",
          "/var/www/html/libraries/"
        ],
        "allow_regex": [
          "/(api|cli)/i"
        ],
        "reject": [
          "/var/www/html/vendor/guzzle"
        ],
        "reject_regex": [
          "/(admin|test)/i"
        ]
      }
    }
  }
}
JSON;

    private const PATHS = [
        "/var/www/html/src/application/bootstrap.php" => 1,
        "/var/www/html/src/application/controllers/IndexController.php" => 2,
        "/var/www/html/src/application/controllers/AdminController.php" => 3,
        "/var/www/html/vendor/guzzle/broken.php" => 4,
        "/var/www/html/vendor/phinx/working.php" => 5,
        "/var/www/html/libraries/ApiProvider/HateOas.php" => 6,
        "/var/www/html/public/phpmyadmin/index.php" => 7,
        "/var/www/html/public/api/index.php" => 8,
    ];

    public function testNoConfigAllowsEverything(): void
    {
        $filter = new PathFilter();
        $this->assertEquals(self::PATHS, iterator_to_array($filter->filterArrayKeys(self::PATHS)));
    }

    public function testComplexConfig(): void
    {
        $filter = PathFilter::fromServiceConfig(new Config(self::CONFIG)->getService('testService'));
        $this->assertEquals(
            [
                "/var/www/html/src/application/bootstrap.php" => 1,
                "/var/www/html/src/application/controllers/IndexController.php" => 2,
                "/var/www/html/vendor/phinx/working.php" => 5,
                "/var/www/html/libraries/ApiProvider/HateOas.php" => 6,
                "/var/www/html/public/api/index.php" => 8,
            ],
            iterator_to_array($filter->filterArrayKeys(self::PATHS))
        );
    }
}
