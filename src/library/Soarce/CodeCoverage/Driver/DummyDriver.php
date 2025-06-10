<?php

namespace Soarce\CodeCoverage\Driver;

use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;

class DummyDriver extends Driver
{
    public function nameAndVersion(): string
    {
        return "Soarce 1.x";
    }

    public function start(): void
    {
        //intentionally left blank, we have everything in the database at this point
    }

    public function stop(): RawCodeCoverageData
    {
        return RawCodeCoverageData::fromXdebugWithoutPathCoverage([]);
    }
}