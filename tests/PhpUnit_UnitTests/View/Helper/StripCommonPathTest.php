<?php

namespace UnitTests;

use PHPUnit\Framework\TestCase;
use Soarce\View\Helper\StripCommonPath;

class StripCommonPathTest extends TestCase
{
    public function testProperReplacement(): void
    {
        $this->assertEquals('./test/me.php', StripCommonPath::filter('/var/www/test/me.php', '/var/www'));
        $this->assertEquals('./test/me.php', StripCommonPath::filter('/var/www/test/me.php', '/var/www/'));
    }

    public function testNoReplacement(): void
    {
        $this->assertEquals('/var/www/test/me.php', StripCommonPath::filter('/var/www/test/me.php', ''));
        $this->assertEquals('/var/www/test/me.php', StripCommonPath::filter('/var/www/test/me.php', '/'));
        $this->assertEquals('/var/www/test/me.php', StripCommonPath::filter('/var/www/test/me.php', '/somewhere/else'));
        $this->assertEquals('/var/www/test/me.php', StripCommonPath::filter('/var/www/test/me.php', '/var/www/partially'));
    }
}
