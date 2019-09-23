<?php

namespace UnitTests;

use PHPUnit\Framework\TestCase;
use Soarce\Model\SequenceRequest;

class SequenceRequestTest extends TestCase
{
    public function testEmptyReturnsNull()
    {
        $this->assertNull(SequenceRequest::buildTree([]));
    }

    public function testOneRequestHasNeitherParentsNorChildren()
    {
        $result = SequenceRequest::buildTree([
            'af3d31f807355e293958983c2d9eb110' => [
                'id'              => 728,
                'request_id'      => 'af3d31f807355e293958983c2d9eb110',
                'request_started' => '2019-09-11 19:58:05.310800',
                'applicationName' => 'billingApplication',
                'applicationId'   => 145,
            ],
        ]);

        $this->assertInstanceOf(SequenceRequest::class, $result);
        $this->assertEquals(728, $result->getId());
        $this->assertEquals('af3d31f807355e293958983c2d9eb110', $result->getRequestId());
        $this->assertEquals('2019-09-11 19:58:05.310800', $result->getStarted()->format('Y-m-d H:i:s.u'));
        $this->assertEquals('billingApplication', $result->getApplicationName());
        $this->assertEquals(145, $result->getApplicationId());

        $this->assertNull($result->getParent());
        $this->assertEquals([], $result->getChildren());
    }


}
