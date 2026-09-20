<?php

namespace Soarce\DataTables;

class Response {
    public function __construct(
        private int $draw,
        private int $recordsTotal,
        private int $recordsFiltered,
        private array $data,
        private ?string $error = null
    ) {}

    public function toJson(): string
    {
        return json_encode([
            'draw' => $this->draw,
            'recordsTotal' => $this->recordsTotal,
            'recordsFiltered' => $this->recordsFiltered,
            'data' => $this->data,
            'error' => $this->error,
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }
}
