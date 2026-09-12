<?php

namespace UnitTests;

use PHPUnit\Framework\TestCase;
use Soarce\Validator\AllowPathsRegexValidator;

class AllowPathsRegexValidatorTest extends TestCase
{
    public function testSingleRegexAllowsPath(): void
    {
        $validator = new AllowPathsRegexValidator(['#vendor#']);
        $this->assertTrue($validator->isValid('/var/www/html/vendor/autoload.php'));
    }

    public function testSingleRegexRejectsPath(): void
    {
        $validator = new AllowPathsRegexValidator(['#vendor#']);
        $this->assertFalse($validator->isValid('/var/www/html/application/bootstrap.php'));
    }

    public function testOneMatchInMultipleRegexAllowsFile(): void
    {
        $validator = new AllowPathsRegexValidator(['#app#', '#vendor#']);
        $this->assertTrue($validator->isValid('/var/www/html/vendor/autoload.php'));
    }

    public function testMultipleRegexThatDontMatchRejectPath(): void
    {
        $validator = new AllowPathsRegexValidator(['#vendor#', '#thirdparty#']);
        $this->assertFalse($validator->isValid('/var/www/html/application/bootstrap.php'));
    }

    public function testEmptyRulesAllowEverything(): void
    {
        $validator = new AllowPathsRegexValidator([]);
        $this->assertTrue($validator->isValid('/var/www/html/application/bootstrap.php'));
    }
}
