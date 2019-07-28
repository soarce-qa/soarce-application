<?php

namespace Soarce\Config;

class Service
{
    /** @var string */
    private $name;

    /** @var string */
    private $url;

    /** @var string */
    private $parameterName;

    /** @var string */
    private $commonPath;

    public function __construct($name, $url, $parameterName, $commonPath)
    {
        $this->name          = $name;
        $this->url           = $url;
        $this->parameterName = $parameterName;
        $this->commonPath    = $commonPath;
    }

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
}
