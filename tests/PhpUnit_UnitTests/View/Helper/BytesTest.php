<?php

namespace UnitTests;

use PHPUnit\Framework\TestCase;
use Soarce\View\Helper\Bytes;

class BytesTest extends TestCase
{
    public function workingCases(): array
    {
        return [
            '0byte'     => ['0 B',                                            0],
            'bytes'     => ['1 B',                                            1],
            'kilobytes' => ['1,2 KiB',                                     1205],
            'megabytes' => ['1,15 MiB',                                 1205141],
            'gigabytes' => ['1,975 GiB',                             2120345141],
            'terabytes' => ['2,0118 TiB',                         2212034451541],
            'petabytes' => ['2,08297 PiB',                     2345212034451541],
            'ebytes'    => ['2,130925 EiB',                 2456789212034451541],
            'zbytes'    => ['2,0809812 ZiB',             2456789012212034451541],
            'ybytes'    => ['2,03220824 YiB',         2456789012212978104451541],
            'nbytes'    => ['1,984578359 NiB',     2456789012212978103364513541],
            'dbytes'    => ['1,9380648041 DiB', 2456789012212978103364474514541],
        ];
    }

    /**
     * @param string    $expected
     * @param int|float $number
     * @dataProvider workingCases
     */
    public function testProperReplacement($expected, $number): void
    {
        if ($number > PHP_INT_MAX) {
            $this->markTestSkipped('We are most probably run as 32bit and/or on windows.');
        }

        $this->assertEquals($expected, Bytes::filter($number));
    }

    public function testCuttingOff(): void
    {
        $this->assertEquals('1 KiB',    Bytes::filter(1024));
        $this->assertEquals('1 MiB',    Bytes::filter(1024*1024));
        $this->assertEquals('1,01 MiB', Bytes::filter(1.01*1024*1024));
        $this->assertEquals('1,1 MiB',  Bytes::filter(1.1*1024*1024));
        $this->assertEquals('1,1 GiB',  Bytes::filter(1.1*1024*1024*1024));
    }

}
