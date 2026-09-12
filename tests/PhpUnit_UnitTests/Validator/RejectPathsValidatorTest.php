<?php

namespace PhpUnit_UnitTests\Validator;

use PHPUnit\Framework\TestCase;
use Soarce\Validator\RejectPathsValidator;

class RejectPathsValidatorTest extends TestCase
{
    public function testSingleRuleAllowsPath(): void
    {
        $validator = new RejectPathsValidator(['/var/www/html/application']);
        $this->assertTrue($validator->isValid('/var/www/html/vendor/autoload.php'));
    }

    public function testSingleRuleRejectsPath(): void
    {
        $validator = new RejectPathsValidator(['/var/www/html/application/']);
        $this->assertFalse($validator->isValid('/var/www/html/application/bootstrap.php'));
    }

    public function testThatPathsNeedToBeAbsolute(): void
    {
        $validator = new RejectPathsValidator(['vendor/']);
        $this->assertTrue($validator->isValid('/var/www/html/vendor/autoload.php'));
    }

    public function testOneMatchInMultipleRejectsFile(): void
    {
        $validator = new RejectPathsValidator(['/var/www/html/application/', '/var/www/html/vendor/']);
        $this->assertFalse($validator->isValid('/var/www/html/vendor/autoload.php'));
    }

    public function testMultipleRulesThatDontMatchAllowPath(): void
    {
        $validator = new RejectPathsValidator(['/var/www/html/application/third_party/', '/var/www/html/vendor/']);
        $this->assertTrue($validator->isValid('/var/www/html/application/bootstrap.php'));
    }

    public function testEmptyRulesAllowEverything(): void
    {
        $validator = new RejectPathsValidator([]);
        $this->assertTrue($validator->isValid('/var/www/html/application/bootstrap.php'));
    }
}
