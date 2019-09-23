<?php

namespace Soarce\Model;

class SequenceRequestHeap extends \SplHeap
{
    /**
     * @param  SequenceRequest $value1
     * @param  SequenceRequest $value2
     * @return int|void
     */
    protected function compare($value1, $value2)
    {
        return strnatcmp($value2->getRequestId(), $value1->getRequestId());
    }
}