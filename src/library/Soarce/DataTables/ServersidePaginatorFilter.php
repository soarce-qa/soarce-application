<?php

namespace Soarce\DataTables;

use Soarce\DataTables\ServersidePaginatorFilter\Column;
use Soarce\DataTables\ServersidePaginatorFilter\Order;
use Soarce\DataTables\ServersidePaginatorFilter\Search;

class ServersidePaginatorFilter
{
    public static function fromPost(array $post): self
    {
        if (isset($post['search']['value'])) {
            $searchGlobal = new Search(
                $post['search']['value'],
                isset($post['search']['regex']) && strtolower($post['search']['regex']) === 'true'
            );
        }

        $order = [];
        if (isset($post['order'])) {
            foreach ($post['order'] as $orderRow) {
                $order[] = new Order($orderRow['column'], $orderRow['dir'], $orderRow['name'] ?? '');
            }
        }

        $columns = [];
        if (isset($post['columns'])) {
            foreach ($post['columns'] as $columnRow) {
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
            $post['draw'] ?? 0,
            $post['start'] ?? 0,
            $post['length'] ?? -1,
            $searchGlobal ?? null,
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
        private ?Search $search  = null,
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

    public function getOrderSQL(array $columnMap = []): string
    {
        $columns = $this->getColumns();
        $order = [];
        foreach ($this->getOrder() as $orderColumn) {
            $column = $columns[$orderColumn->getColumn()];
            if (!isset($columnMap[$column->getName()])) {
                throw new \RuntimeException('column name not found in columnMap, cannot map to database columns');
            }
            $order[] = $columnMap[$column->getName()] . ' ' . $orderColumn->getDir();
        }
        return implode (", ", $order);
    }
}