<?php

namespace Soarce\DataTables\ServersidePaginatorFilter;
class Search
{
    public function __construct(private string $value, private bool $regex = false)
    {}

    public function getValue(): string
    {
        return $this->value;
    }

    public function isRegex(): bool
    {
        return $this->regex;
    }
}