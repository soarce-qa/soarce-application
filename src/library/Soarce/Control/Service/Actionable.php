<?php

namespace Soarce\Control\Service;

use Soarce\Config\Service;

class Actionable
{
    private const PING__EXPECTED_RESPONSE = 'pong';

    /** @var Service */
    private $serviceConfig;

    /** @var bool[] */
    private $preconditions;

    /**
     * Actionable constructor.
     *
     * @param Service $serviceConfig
     */
    public function __construct(Service $serviceConfig)
    {
        $this->serviceConfig = $serviceConfig;
    }

    /**
     * @return Service
     */
    public function getServiceConfig(): Service
    {
        return $this->serviceConfig;
    }

    /**
     * @return bool
     */
    public function ping(): bool
    {
        return self::PING__EXPECTED_RESPONSE === file_get_contents($this->buildUrl('ping'));
    }

    /**
     *
     */
    public function collectPreconditions(): void
    {
       $this->preconditions = json_decode(file_get_contents($this->buildUrl('preconditions')), JSON_OBJECT_AS_ARRAY);
    }

    /**
     * @return bool[]
     */
    public function getPreconditions(): array
    {
        return $this->preconditions;
    }

    /**
     * @param  string $action
     * @return string
     */
    private function buildUrl($action): string
    {
        return $this->serviceConfig->getUrl()
            . '?'
            . $this->serviceConfig->getParameterName()
            . '='
            . $action;
    }

}
