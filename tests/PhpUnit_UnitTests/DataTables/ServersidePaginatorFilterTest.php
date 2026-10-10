<?php

namespace UnitTests;

use PHPUnit\Framework\TestCase;
use Soarce\DataTables\ServersidePaginatorFilter;

class ServersidePaginatorFilterTest extends TestCase {

    public function testRealExample(): void
    {
        $post = [
            'draw' => '1',
            'columns' => [
                0 => ['data' => '0', 'name' => 'application', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false',],],
                1 => ['data' => '1', 'name' => 'file', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => 'qwer', 'regex' => 'false',],],
                2 => ['data' => '2', 'name' => 'lines', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false',],],
                3 => ['data' => '3', 'name' => 'coveredLines', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false',],],
                4 => ['data' => '4', 'name' => 'percentageBar', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false',],],
                5 => ['data' => '5', 'name' => 'percentageNumber', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false',],],
            ],
            'order' => [
                0 => ['column' => '0', 'dir' => 'asc',],
                1 => ['column' => '4', 'dir' => 'desc',],
            ],
            'start' => '0',
            'length' => '25',
            'search' => ['value' => 'asdf', 'regex' => 'false',],
        ];

        $spf = ServersidePaginatorFilter::fromPost($post);

        $this->assertEquals('`application_id` asc, `coverage` desc', $spf->getOrderSQL([
            'application' => 'application_id',
            'percentageBar' => 'coverage',
        ]));

        $this->assertEquals(0, $spf->getStart());
        $this->assertEquals(25, $spf->getLength());

        $this->assertEquals('asdf', $spf->getSearch()->getValue());
        $this->assertFalse($spf->getSearch()->isRegex(), print_r($spf->getSearch(), true));
    }
}