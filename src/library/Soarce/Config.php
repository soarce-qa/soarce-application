<?php

namespace Soarce;

use Soarce\Config\Service;

class Config
{
    private array $services = [];

    public function __construct(string $filename)
    {
        foreach (json_decode(file_get_contents($filename), JSON_OBJECT_AS_ARRAY)['services'] as $name => $rawService) {
            $this->services[$name] = new Service($name, $rawService['url'], $rawService['parameter_name'], $rawService['common_path'], $rawService['preshared_secret']);
        }
    }

    public function getService(string $name): Service
    {
        return $this->services[$name];
    }

    public function getServices(): array
    {
        return $this->services;
    }
}