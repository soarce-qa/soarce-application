<?php

namespace Soarce;

use Soarce\Config\Service;
use Swaggest\JsonSchema\Schema;

class Config
{
    /** @var string */
    public static $validationError;

    /**
     * @param  string $filename
     * @return bool
     */
    public static function isValid($filename): bool
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
     * @param  string $filename
     * @return Service[]
     */
    public static function load($filename): array
    {
        $services = [];
        foreach (json_decode(file_get_contents($filename), JSON_OBJECT_AS_ARRAY) as $rawService) {
            $services[] = new Service($rawService['name'], $rawService['url'], $rawService['parameter_name']);
        }

        return $services;
    }
}