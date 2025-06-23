<?php

namespace Soarce\DataTables;

use Soarce\DataTables\ServersidePaginatorFilter\Column;
use Soarce\DataTables\ServersidePaginatorFilter\Order;
use Soarce\DataTables\ServersidePaginatorFilter\Search;

class ServersidePaginatorFilter
{
    public static function fromJson(string $json): self
    {
        $raw = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (isset($raw['search']['value'])) {
            $search = new Search($raw['search']['value'], $raw['search']['regex'] ?? false);
        }

        $order = [];
        if (isset($raw['order'])) {
            foreach ($raw['order'] as $orderRow) {
                $order[] = new Order($orderRow['column'], $orderRow['dir'], $orderRow['name'] ?? '');
            }
        }

        $columns = [];
        if (isset($raw['columns'])) {
            foreach ($raw['columns'] as $columnRow) {
                if (isset($columnRow['search']['value'])) {
                    $search = new Search($columnRow['search']['value'], $columnRow['search']['regex'] ?? false);
                } else {
                    $search = null;
                }

                $columns[] = new Column(
                    $columnRow['data'],
                    $columnRow['name'],
                    $columnRow['searchable'],
                    $columnRow['orderable'],
                    $search,
                );
            }
        }

        return new self(
            $raw['draw'] ?? 0,
            $raw['start'] ?? 0,
            $raw['length'] ?? -1,
            $search ?? null,
            $order,
            $columns,
        );
    }

    /**
     * @param int      $draw
     * @param int      $start
     * @param int      $length
     * @param ?Search  $search
     * @param Order[]  $order
     * @param Column[] $columns
     */
    public function __construct(
        private int     $draw    = 0,
        private int     $start   = 0,
        private int     $length  = -1,
        private ?Search $search,
        private array   $order   = [],
        private array   $columns = []
    ) {}

    public function getDraw(): int
    {
        return $this->draw;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getSearch(): ?Search
    {
        return $this->search;
    }

    /**
     * @return Order[]
     */
    public function getOrder(): array
    {
        return $this->order;
    }

    /**
     * @return Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

}