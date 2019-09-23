<?php

namespace Soarce\Model;

use DateTimeImmutable;
use Exception;

class SequenceRequest
{
    /** @var int */
    private $id;

    /** @var string */
    private $requestId;

    /** @var DateTimeImmutable */
    private $started;

    /** @var int */
    private $applicationId;

    /** @var string */
    private $applicationName;

    /** @var self|null */
    private $parent;

    /** @var SequenceRequestHeap */
    private $children;

    /**
     * @param  array $flatList
     * @return static|null
     * @throws Exception
     */
    public static function buildTree(array $flatList): ?self
    {
        if ($flatList === []) {
            return null;
        }

        $internalLookupList = [];
        $rootKey = key($flatList);

        foreach ($flatList as $rawRequest) {
            $request = new self($rawRequest);
            $internalLookupList[$request->getRequestId()] = $request;

        }

        return $internalLookupList[$rootKey];
    }

    /**
     * SequenceRequest constructor.
     *
     * @param  array $rawRequest
     * @throws Exception
     */
    private function __construct(array $rawRequest)
    {
        $this->id              = $rawRequest['id'];
        $this->requestId       = $rawRequest['request_id'];
        $this->started         = new DateTimeImmutable($rawRequest['request_started']);
        $this->applicationId   = $rawRequest['applicationId'];
        $this->applicationName = $rawRequest['applicationName'];

        $this->children = new SequenceRequestHeap();
    }

    /**
     * @param SequenceRequest $parent
     */
    public function setParent(self $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @param SequenceRequest $child
     */
    public function addChild(self $child): void
    {
        $child->setParent($this);
        $this->children->insert($child);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getStarted(): DateTimeImmutable
    {
        return $this->started;
    }

    /**
     * @return int
     */
    public function getApplicationId(): int
    {
        return $this->applicationId;
    }

    /**
     * @return string
     */
    public function getApplicationName(): string
    {
        return $this->applicationName;
    }

    /**
     * @return SequenceRequest|null
     */
    public function getParent(): ?SequenceRequest
    {
        return $this->parent;
    }

    /**
     * @return SequenceRequest[]
     */
    public function getChildren(): array
    {
        $temp = [];
        /** @var SequenceRequest $child */
        foreach ($this->children as $child) {
            $temp[$child->getRequestId()] = $child;
        }

        return $temp;
    }
}
