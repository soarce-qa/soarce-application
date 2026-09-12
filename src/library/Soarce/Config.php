<?php

namespace Soarce;

use Soarce\Config\Service;

class Config
{
    private array $services = [];

    public function __construct(string $json)
    {
        foreach (json_decode($json, JSON_OBJECT_AS_ARRAY)['services'] as $name => $rawService) {
            $this->services[$name] = new Service(
                $name,
                $rawService['url'],
                $rawService['parameter_name'],
                $rawService['common_path'],
                $rawService['preshared_secret'] ?? '',
                $rawService['filters'] ?? []
            );
        }
    }

    public static function fromFile(string $filename): self
    {
        return new self(file_get_contents($filename));
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