<?php

namespace PhpUnit_UnitTests\Validator;

use PHPUnit\Framework\TestCase;
use Soarce\Validator\RejectPathsRegexValidator;

class RejectPathsRegexValidatorTest extends TestCase
{
    public function testSingleRegexAllowsPath(): void
    {
        $validator = new RejectPathsRegexValidator(['#application#']);
        $this->assertTrue($validator->isValid('/var/www/html/vendor/autoload.php'));
    }

    public function testSingleRegexRejectsPath(): void
    {
        $validator = new RejectPathsRegexValidator(['#vendor#']);
        $this->assertFalse($validator->isValid('/var/www/html/vendor/bootstrap.php'));
    }

    public function testOneMatchInMultipleRegexRejectsFile(): void
    {
        $validator = new RejectPathsRegexValidator(['#app#', '#vendor#']);
        $this->assertFalse($validator->isValid('/var/www/html/vendor/autoload.php'));
    }

    public function testMultipleRegexThatDontMatchAllowPath(): void
    {
        $validator = new RejectPathsRegexValidator(['#vendor#', '#thirdparty#']);
        $this->assertTrue($validator->isValid('/var/www/html/application/bootstrap.php'));
    }

    public function testEmptyRulesAllowEverything(): void
    {
        $validator = new RejectPathsRegexValidator([]);
        $this->assertTrue($validator->isValid('/var/www/html/application/bootstrap.php'));
    }
}
