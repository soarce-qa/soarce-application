<?php

namespace Soarce\DataTables\ServersidePaginatorFilter;

class Order
{
    public const string DIR_ASC = 'asc';
    public const string DIR_DESC = 'desc';

    public function __construct(private readonly int $column, private readonly string $dir = self::DIR_ASC, private readonly string $name = '')
    {
        if ($this->dir !== self::DIR_ASC && $this->dir !== self::DIR_DESC) {
            throw new \InvalidArgumentException('Invalid order direction');
        }
    }
}