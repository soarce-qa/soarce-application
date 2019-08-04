<?php

namespace Soarce\Control\Service;

class FileContent
{
    /** @var string (hex) */
    private $md5;

    /** @var string[] */
    private $lines;

    /**
     * FileContent constructor.
     *
     * @param string[] $lines
     * @param string   $md5
     */
    public function __construct(array $lines, $md5)
    {
        $this->lines = $lines;
        $this->md5   = $md5;
    }

    /**
     * @return string
     */
    public function getMd5(): string
    {
        return $this->md5;
    }

    /**
     * @return string[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }
}