<?php

namespace Soarce\Config;

class Service
{
    public function __construct(private string $name, private string $url, private string $parameterName, private string $commonPath, private string $presharedSecret)
    {}

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getParameterName(): string
    {
        return $this->parameterName;
    }

    /**
     * @return string
     */
    public function getCommonPath(): string
    {
        return $this->commonPath;
    }

    /**
     * @return string
     */
    public function getPresharedSecret(): string
    {
        return $this->presharedSecret;
    }
}
