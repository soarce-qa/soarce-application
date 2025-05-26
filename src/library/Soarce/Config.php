<?php

namespace Soarce;

use Soarce\Config\Service;
use Swaggest\JsonSchema\Schema;

class Config
{
    /** @var string */
    public static string $validationError;

    /**
     * @param string $filename
     * @return bool
     */
    public static function isValid(string $filename): bool
    {
        return true;
/*        try {
            $schema = Schema::import(json_decode(file_get_contents(__DIR__ . '/../../fixtures/schema.json')));
            $schema->in(json_decode(file_get_contents($filename)));
            return true;
        } catch (\Exception $e) {
            self::$validationError = $e->getMessage();
            return false;
        }*/
    }

    /**
     * @param string $filename
     * @return Service[]
     */
    public static function load(string $filename): array
    {
        $services = [];
        foreach (json_decode(file_get_contents($filename), JSON_OBJECT_AS_ARRAY)['services'] as $name => $rawService) {
            $services[$name] = new Service($name, $rawService['url'], $rawService['parameter_name'], $rawService['common_path'], $rawService['preshared_secret']);
        }

        return $services;
    }
}