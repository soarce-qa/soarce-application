<?php

namespace UnitTests;

use Soarce\Config;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    public function testFullConfigFromFile(): void
    {
        $config = Config::fromFile(__DIR__ . '/../fixtures/soarce.json');
        $this->assertIsArray($config->getServices());

        $service = $config->getService('testService');
        $this->assertInstanceOf(Config\Service::class, $service);

        $this->assertEquals('testService', $service->getName());
        $this->assertEquals('http://test-service.example.com/', $service->getUrl());
        $this->assertEquals('SOARCE', $service->getParameterName());
        $this->assertEquals('/var/www/html', $service->getCommonPath());
        $this->assertEquals('yo-mama!', $service->getPresharedSecret());
        $this->assertEquals([
            "allow" => [
                "/var/www/html/src/application/",
                "/var/www/html/vendor/",
                "/var/www/html/libraries/"
            ],
                "allow_regex" => [
                "/(api|cli)/i"
            ],
                "reject" => [
                "/var/www/html/vendor/guzzle"
            ],
                "reject_regex" => [
                "/(admin|test)/i"
            ]
        ], $service->getFilters());
    }

    public function testOmittingOptionalsDoesntBreak(): void
    {
        $config = Config::fromFile(__DIR__ . '/../fixtures/soarce-min.json');

        $service = $config->getService('testService');
        $this->assertInstanceOf(Config\Service::class, $service);

        $this->assertEquals('testService', $service->getName());
        $this->assertEquals('http://test-service.example.com/', $service->getUrl());
        $this->assertEquals('SOARCE', $service->getParameterName());
        $this->assertEquals('/var/www/html', $service->getCommonPath());
        $this->assertEquals('', $service->getPresharedSecret());

        $this->assertIsArray($service->getFilters());
        $this->assertEmpty($service->getFilters());
    }

}