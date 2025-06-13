<?php

namespace Soarce;

use Redis;

class QueueManager
{
    public const string QUEUE = "ingres-queue";

    private const string SEPARATOR = '/!\SOARCE-SPLITTING-IT-GOOD/!\\';

    public function __construct(private readonly Redis $redis, private readonly \mysqli $mysqli)
    {}

    protected function getActiveUsecaseId(): int
    {
        $result = $this->mysqli->query('SELECT `id` FROM `usecase` WHERE `active` = 1');
        if ($result->num_rows === 0) {
            throw new \RuntimeException("No active usecase found");
        }
        return $result->fetch_assoc()['id'];
    }

    public function store(string $payload): void
    {
        $this->redis->lPush(
            self::QUEUE,
            $this->getActiveUsecaseId() . self::SEPARATOR . $payload
        );
    }

    public function retrieve(int $timeout): ?array
    {
        $temp = $this->redis->brPop(self::QUEUE, $timeout);
        if (null === $temp || $temp === []) {
            return null;
        }

        if (!is_array($temp)) {
            throw new \RuntimeException('array expected, ' . get_debug_type($temp) . ' given.');
        }

        return explode(self::SEPARATOR, $temp[1], 2);
    }

    public function getQueueSize(): int
    {
        return $this->redis->lLen(self::QUEUE);
    }
}