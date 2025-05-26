<?php

namespace Soarce\Control\Service;

class FileContent
{
    /**
     * FileContent constructor.
     *
     * @param string[] $lines
     * @param string   $md5
     */
    public function __construct(private array $lines, private string $md5)
    {}

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