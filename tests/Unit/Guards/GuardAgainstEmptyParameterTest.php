<?php

namespace Spin8\Tests\Unit\Guards;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Guards\GuardAgainstEmptyParameter;
use Spin8\Tests\TestCase;

#[CoversClass(GuardAgainstEmptyParameter::class)]
final class GuardAgainstEmptyParameterTest extends TestCase {
    #[Test]
    public function test_it_does_not_check_numeric() : void {
        $this->expectNotToPerformAssertions();

        GuardAgainstEmptyParameter::check(0);
        GuardAgainstEmptyParameter::check(0.1);
        GuardAgainstEmptyParameter::check(0.0);
        GuardAgainstEmptyParameter::check(-0.0);
        GuardAgainstEmptyParameter::check('0');
        GuardAgainstEmptyParameter::check((int) '');
        GuardAgainstEmptyParameter::check((string) 0);
    }

    #[Test]
    public function test_it_does_not_check_null_if_allow_null_is_true() : void {
        $this->expectNotToPerformAssertions();

        GuardAgainstEmptyParameter::check(null, allow_null: true);
    }

    #[Test]
    public function test_it_does_check_null_if_allow_null_is_false() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("function '" . __FUNCTION__ . "' was called with non-allowed empty argument");

        GuardAgainstEmptyParameter::check(null, allow_null: false);
    }

    #[Test]
    public function test_it_does_not_check_bool() : void {
        $this->expectNotToPerformAssertions();

        GuardAgainstEmptyParameter::check(true);
        GuardAgainstEmptyParameter::check(false);
    }

    #[Test]
    public function test_it_throws_InvalidArgumentException_if_parameter_to_check_is_empty_string() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("function '" . __FUNCTION__ . "' was called with non-allowed empty argument");

        GuardAgainstEmptyParameter::check('');
    }

    #[Test]
    public function test_it_throws_InvalidArgumentException_if_parameter_to_check_is_empty_array() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("function '" . __FUNCTION__ . "' was called with non-allowed empty argument");

        GuardAgainstEmptyParameter::check([]);
    }

    #[Test]
    public function test_it_correctly_builds_backtrace_message() : void {
        $reflector = new \ReflectionClass(\PHPUnit\Framework\TestCase::class);
        $file = $reflector->getFileName();

        $message = "function '" . __FUNCTION__ . "' was called with non-allowed empty argument in {$file} on line 1116." . PHP_EOL . PHP_EOL;
        $message .= 'Passed arguments:' . PHP_EOL . '[]';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        GuardAgainstEmptyParameter::check('');
    }
}
