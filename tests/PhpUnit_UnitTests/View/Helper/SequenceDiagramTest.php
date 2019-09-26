<?php

namespace UnitTests;

use PHPUnit\Framework\TestCase;
use Soarce\Model\SequenceRequest;
use Soarce\View\Helper\SequenceDiagram;

class SequenceDiagramTest extends TestCase
{
    public function testProperReplacement(): void
    {
        $expectedResult = <<<MERMAID
sequenceDiagram
    participant Client
    participant billingApplication
    participant clientService
    participant invoiceService
    participant addingService
    Client->>billingApplication: af3d31f807355e293958983c2d9eb110
    billingApplication->>clientService: af3d31f807355e293958983c2d9eb110-1
    clientService->>billingApplication: return
    billingApplication->>invoiceService: af3d31f807355e293958983c2d9eb110-2
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-1
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-2
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-3
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-4
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-5
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-6
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-7
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-8
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-9
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-10
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-11
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-12
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-13
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-14
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-15
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-16
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-17
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-18
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-19
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-20
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-21
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-22
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-23
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-24
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-25
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-26
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-27
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-28
    addingService->>invoiceService: return
    invoiceService->>addingService: af3d31f807355e293958983c2d9eb110-2-29
    addingService->>invoiceService: return
    invoiceService->>billingApplication: return
    billingApplication->>Client: return

MERMAID;

        $expectedResult = str_replace("\r", '', $expectedResult);

        $requests = json_decode(
            file_get_contents(__DIR__ . '/../../../fixtures/sequence.json'),
            JSON_OBJECT_AS_ARRAY
        );

        $sequence = SequenceRequest::buildTree($requests);

        $applications = array_unique(array_column($requests, 'applicationName', 'applicationId'));

        $this->assertEquals($expectedResult, SequenceDiagram::filter($sequence, $applications));
    }
}
