<?php

namespace UnitTests;

use PHPUnit\Framework\TestCase;
use Soarce\Model\SequenceRequest;

class SequenceRequestTest extends TestCase
{
    public function testEmptyReturnsNull(): void
    {
        $this->assertNull(SequenceRequest::buildTree([]));
    }

    public function testOneRequestHasNeitherParentsNorChildren(): void
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

        $this->assertParentInstanceValues($result);

        $this->assertEquals([], $result->getChildren());
    }

    public function testWithOneChildRequest(): void
    {
        $result = SequenceRequest::buildTree([
            'af3d31f807355e293958983c2d9eb110' => [
                'id'              => 728,
                'request_id'      => 'af3d31f807355e293958983c2d9eb110',
                'request_started' => '2019-09-11 19:58:05.310800',
                'applicationName' => 'billingApplication',
                'applicationId'   => 145,
            ],
            'af3d31f807355e293958983c2d9eb110-1' => [
                'id'              => 729,
                'request_id'      => 'af3d31f807355e293958983c2d9eb110-1',
                'request_started' => '2019-09-11 19:58:05.327000',
                'applicationName' => 'clientService',
                'applicationId'   => 1337,
            ],
        ]);

        $this->assertParentInstanceValues($result);

        $children = $result->getChildren();
        $this->assertCount(1, $children);

        $child = array_pop($children);

        $this->assertInstanceOf(SequenceRequest::class,           $child);
        $this->assertEquals(729,                                  $child->getId());
        $this->assertEquals('af3d31f807355e293958983c2d9eb110-1', $child->getRequestId());
        $this->assertEquals('2019-09-11 19:58:05.327000',         $child->getStarted()->format('Y-m-d H:i:s.u'));
        $this->assertEquals('clientService',                      $child->getApplicationName());
        $this->assertEquals(1337,                                 $child->getApplicationId());

        $this->assertSame($result, $child->getParent());
    }

    public function testMultipleLevelsExample(): void
    {
        $result = SequenceRequest::buildTree(
            json_decode(
                file_get_contents(__DIR__ . '/../../fixtures/sequence.json'),
                JSON_OBJECT_AS_ARRAY
            )
        );

        $this->assertParentInstanceValues($result);

        $children = $result->getChildren();

        $this->assertCount(2, $children);

        $secondChild = array_pop($children);
        $subChildren = $secondChild->getChildren();

        $this->assertCount(29, $subChildren);
        $this->assertEquals(
            $this->getRequestRange(),
            array_keys($subChildren)
        );
    }

    /**
     * @return string[]
     */
    private function getRequestRange(): array
    {
        $ret = [];
        foreach (range(1, 29) as $num) {
            $ret[] = 'af3d31f807355e293958983c2d9eb110-2-' . $num;
        }
        return $ret;
    }

    /**
     * @param SequenceRequest|null $result
     */
    private function assertParentInstanceValues(?SequenceRequest $result): void
    {
        $this->assertNull($result->getParent());

        $this->assertInstanceOf(SequenceRequest::class,         $result);
        $this->assertEquals(728,                                $result->getId());
        $this->assertEquals('af3d31f807355e293958983c2d9eb110', $result->getRequestId());
        $this->assertEquals('2019-09-11 19:58:05.310800',       $result->getStarted()->format('Y-m-d H:i:s.u'));
        $this->assertEquals('billingApplication',               $result->getApplicationName());
        $this->assertEquals(145,                                $result->getApplicationId());
    }
}
