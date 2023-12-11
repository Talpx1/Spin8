<?php

namespace Spin8\Tests\Unit\Guards;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Spin8\Guards\GuardAgainstNonExistingClassString;
use Spin8\Tests\TestCase;

#[CoversClass(GuardAgainstNonExistingClassString::class)]
final class GuardAgainstNonExistingClassStringTest extends TestCase {
    #[Test]
    public function test_it_trows_if_passed_value_is_not_a_valid_class_string() : void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("'test' does not reference a valid class.");

        GuardAgainstNonExistingClassString::check('test');
    }

    #[Test]
    public function test_it_does_not_trow_if_passed_value_is_a_valid_class_string() : void {
        $this->expectNotToPerformAssertions();

        GuardAgainstNonExistingClassString::check(\ArrayObject::class);
    }

    #[Test]
    public function test_it_trows_if_passed_value_is_not_a_valid_class_string_or_interface_string() : void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("'test' does not reference a valid class.");

        GuardAgainstNonExistingClassString::check('test', consider_interfaces: true);
    }

    #[Test]
    public function test_it_does_not_trow_if_passed_value_is_a_valid_interface_string_when_consider_interface_is_true() : void {
        $this->expectNotToPerformAssertions();

        GuardAgainstNonExistingClassString::check(\Throwable::class, consider_interfaces: true);
    }

    #[Test]
    public function test_it_trows_if_passed_value_is_a_valid_interface_string_when_consider_interface_is_false() : void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("'" . \Throwable::class . "' does not reference a valid class.");

        GuardAgainstNonExistingClassString::check(\Throwable::class, consider_interfaces: false);
    }

    #[Test]
    public function test_it_correctly_builds_backtrace_message() : void {
        $reflector = new \ReflectionClass(\PHPUnit\Framework\TestCase::class);
        $file = $reflector->getFileName();

        $message = "'test' does not reference a valid class." . PHP_EOL;
        $message .= "Thrown in function '" . __FUNCTION__ . "'";
        $message .= " called in {$file} on line 1114." . PHP_EOL;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);

        GuardAgainstNonExistingClassString::check('test');
    }

    #[Test]
    public function test_it_uses_RuntimeException_if_not_provided_with_throwable() : void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("'test' does not reference a valid class.");

        GuardAgainstNonExistingClassString::check('test');
    }

    #[Test]
    public function test_it_throws_provided_throwable_class_string() : void {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("'test' does not reference a valid class.");

        GuardAgainstNonExistingClassString::check('test', throwable: \LogicException::class);
    }

    #[Test]
    public function test_it_throws_provided_throwable_callback() : void {
        $this->expectException(\LogicException::class);
        $this->expectExceptionCode(123);
        $this->expectExceptionMessage("'test' does not reference a valid class.");

        GuardAgainstNonExistingClassString::check('test', throwable: fn ($message) => new \LogicException($message, 123));
    }

    #[Test]
    public function test_it_throws_RuntimeException_if_provided_throwable_is_not_a_valid_class_string() : void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('$throwable must be a valid instance of ' . \Throwable::class . ". 'test' passed.");

        // @phpstan-ignore-next-line
        GuardAgainstNonExistingClassString::check('test', 'test');
    }

    #[Test]
    public function test_it_throws_RuntimeException_if_provided_throwable_does_not_subclass_Throwable_interface() : void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('$throwable must be a valid instance of ' . \Throwable::class . ". '" . \ArrayObject::class . "' passed.");

        // @phpstan-ignore-next-line
        GuardAgainstNonExistingClassString::check('test', \ArrayObject::class);
    }
}
