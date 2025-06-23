<?php

namespace Soarce\DataTables\ServersidePaginatorFilter;

class Column
{
    public function __construct(
        private readonly string  $data = '',
        private readonly string  $name = '',
        private readonly bool    $searchable = true,
        private readonly bool    $orderable = true,
        private readonly ?Search $search)
    {}

    public function getData(): string
    {
        return $this->data;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isOrderable(): bool
    {
        return $this->orderable;
    }

    public function getSearch(): ?Search
    {
        return $this->search;
    }
}
