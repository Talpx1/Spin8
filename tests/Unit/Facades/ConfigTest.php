<?php

namespace Spin8\Tests\Unit\Facades;

use BadMethodCallException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Configs\Exceptions\ConfigFileNotLoadedException;
use Spin8\Configs\Exceptions\ConfigKeyMissingException;
use Spin8\Facades\Config;
use Spin8\Facades\Facade;
use Spin8\Tests\TestCase;

#[CoversClass(Config::class)]
#[CoversClass(Facade::class)]
final class ConfigTest extends TestCase {

    #[Test]
    public function test_can_get_config(): void {
        $this->configRepository()->set('test.abc', 123);
        $this->configRepository()->set('test.def', 456);

        $this->configRepository()->set('test2.abc', 123);
        $this->configRepository()->set('test2.def', 456);

        $this->assertSame(123, Config::get('test.abc'));
        $this->assertSame(456, Config::get('test.def'));

        $this->assertSame(123, Config::get('test2.abc'));
        $this->assertSame(456, Config::get('test2.def'));
    }

    #[Test]
    public function test_if_throws_ConfigKeyMissingException_if_config_key_does_not_exists(): void {
        $this->configRepository()->set('test.abc', 123);
        $this->assertSame(123, Config::get('test.abc'));
        
        $this->expectException(ConfigKeyMissingException::class);
        Config::get('test.def');        
    }

    #[Test]
    public function test_can_set_config(): void {
        Config::set('test.abc', 123);
        Config::set('test.def', 456);

        Config::set('test2.abc', 123);
        Config::set('test2.def', 456);

        $this->assertSame(123, Config::get('test.abc'));
        $this->assertSame(456, Config::get('test.def'));

        $this->assertSame(123, Config::get('test2.abc'));
        $this->assertSame(456, Config::get('test2.def'));
    }

    #[Test]
    public function test_it_can_check_if_config_file_has_config_key(): void {
        Config::set('test.abc', 123);
        
        $this->assertTrue(Config::has('test.abc'));
        $this->assertFalse(Config::has('test.def'));
    }

    #[Test]
    public function test_it_can_get_config_config_providing_fallback(): void {
        Config::set('test.abc', 123);

        $this->assertSame(123, Config::getOr('test.abc', 456));
    }

    #[Test]
    public function test_it_returns_the_provided_fallback_if_config_key_does_not_exists(): void {
        Config::set('test.abc', 123);
        $this->assertSame(456, Config::getOr('test.def', 456));
        $this->assertNull( Config::getOr('test.def'));
    }

    #[Test]
    public function test_it_returns_the_provided_fallback_if_config_file_does_not_exists(): void {
        $this->assertSame(456, Config::getOr('test.abc', 456));
        $this->assertNull(Config::getOr('test.abc'));
    }

    #[Test]
    public function test_it_throws_InvalidArgumentException_if_passed_config_key_is_empty_string_while_getting_config_with_fallback(): void {
        $this->expectException(InvalidArgumentException::class);
        Config::getOr('', 'abc');
    }

    #[Test]
    public function test_it_can_call_all_allowed_methods(): void {
        try{ Config::get('test'); } catch(ConfigKeyMissingException){}
        Config::set('test', 'abc');
        Config::setFrom(['a'=>'b']);
        Config::has('test');
        Config::getOr('test', 'abc');
        Config::clear();

        $this->expectNotToPerformAssertions();
    }
    
    #[Test]
    public function test_it_throws_BadMethodCallException_calling_non_allowed_method(): void {
        $this->expectException(BadMethodCallException::class);
        Config::loadAll();
    }
    
}
