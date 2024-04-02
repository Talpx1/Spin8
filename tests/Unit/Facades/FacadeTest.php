<?php

namespace Spin8\Tests\Unit\Facades;

use BadMethodCallException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Facades\Facade;
use Spin8\Tests\TestCase;
use TypeError;

#[CoversClass(FacadeTest::class)]
final class FacadeTest extends TestCase {

    #[Test]
    public function test_it_skips_method_existence_check_if_implementor_uses_mixins_and_method_is_allowed(): void {
        $implementor = new class{};

        container()->alias('implementor', $implementor::class);

        $facade = new class extends Facade{
            protected static array $allowed = ['testMethod'];
            protected static bool $implementor_uses_mixins = true;

            protected static function implementor() : string {
                return 'implementor';
            }
        };

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('call_user_func_array(): Argument #1 ($callback) must be a valid callback, class class@anonymous does not have a method "testMethod"');

        $facade::testMethod();
    }

    #[Test]
    public function test_it_performs_method_existence_check_if_implementor_does_not_use_mixins_and_method_is_allowed_but_non_existing(): void {
        $implementor = new class{};

        container()->alias('implementor', $implementor::class);

        $facade = new class extends Facade{
            protected static array $allowed = ['testMethod'];
            protected static bool $implementor_uses_mixins = false;

            protected static function implementor() : string {
                return 'implementor';
            }
        };

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage("does not have a method called testMethod");

        $facade::testMethod();
    }

    #[Test]
    public function test_calls_method_if_implementor_does_not_use_mixins_and_method_is_allowed_and_existing(): void {
        $implementor = new class {
            public function testMethod(): string { return 'called'; }
        };

        container()->alias('implementor', $implementor::class);

        $facade = new class extends Facade{
            protected static array $allowed = ['testMethod'];
            protected static bool $implementor_uses_mixins = false;

            protected static function implementor() : string {
                return 'implementor';
            }
        };

        $this->assertSame('called', $facade::testMethod());
    }

    #[Test]
    public function test_it_throws_BadMethodCallException_if_called_method_is_not_allowed(): void {
        $implementor = new class{
            public function testMethod1(): string { return 'called1'; }
            public function testMethod2(): string { return 'called2'; }
        };

        container()->alias('implementor', $implementor::class);

        $facade = new class extends Facade{
            protected static array $allowed = ['testMethod1'];

            protected static function implementor() : string {
                return 'implementor';
            }
        };

        $this->assertSame('called1', $facade::testMethod1());

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage("does not have a method called testMethod");

        $facade::testMethod2();
    }

    #[Test]
    public function test_it_allows_all_methods_using_star(): void {
        $implementor = new class{
            public function testMethod1(): string { return 'called1'; }
            public function testMethod2(): string { return 'called2'; }
        };

        container()->alias('implementor', $implementor::class);

        $facade = new class extends Facade{
            protected static array $allowed = ['*'];

            protected static function implementor() : string {
                return 'implementor';
            }
        };

        $this->assertSame('called1', $facade::testMethod1());
        $this->assertSame('called2', $facade::testMethod2());
    }
    
}
