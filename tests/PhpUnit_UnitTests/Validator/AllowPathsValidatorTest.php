<?php

namespace PhpUnit_UnitTests\Validator;

use PHPUnit\Framework\TestCase;
use Soarce\Validator\AllowPathsValidator;

class AllowPathsValidatorTest extends TestCase
{
    public function testSingleRuleAllowsPath(): void
    {
        $validator = new AllowPathsValidator(['/var/www/html/']);
        $this->assertTrue($validator->isValid('/var/www/html/vendor/autoload.php'));
    }

    public function testSingleRuleRejectsPath(): void
    {
        $validator = new AllowPathsValidator(['/var/www/html/vendor/']);
        $this->assertFalse($validator->isValid('/var/www/html/application/bootstrap.php'));
    }

    public function testThatPathsNeedToBeAbsolute(): void
    {
        $validator = new AllowPathsValidator(['vendor/']);
        $this->assertFalse($validator->isValid('/var/www/html/vendor/autoload.php'));
    }

    public function testOneMatchInMultipleAllowsFile(): void
    {
        $validator = new AllowPathsValidator(['/var/www/html/application/', '/var/www/html/vendor/']);
        $this->assertTrue($validator->isValid('/var/www/html/vendor/autoload.php'));
    }

    public function testMultipleRulesThatDontMatchRejectPath(): void
    {
        $validator = new AllowPathsValidator(['/var/www/html/application/third_party/', '/var/www/html/vendor/']);
        $this->assertFalse($validator->isValid('/var/www/html/application/bootstrap.php'));
    }

    public function testEmptyRulesAllowEverything(): void
    {
        $validator = new AllowPathsValidator([]);
        $this->assertTrue($validator->isValid('/var/www/html/application/bootstrap.php'));
    }
}
