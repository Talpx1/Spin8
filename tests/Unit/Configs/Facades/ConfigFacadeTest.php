<?php

namespace Spin8\Tests\Unit;

use Error;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Spin8\Configs\ConfigRepository;
use Spin8\Configs\Exceptions\ConfigFileMissingException;
use Spin8\Configs\Exceptions\ConfigFileNotReadableException;
use Spin8\Configs\Exceptions\ConfigKeyMissingException;
use Spin8\Configs\Facades\ConfigFacade;
use Spin8\Spin8;
use Spin8\Tests\TestCase;

#[CoversClass(ConfigFacade::class)]
final class ConfigFacadeTest extends TestCase {

    #[Test]
    public function test_can_get_config(): void {
        $this->config_repository->set('test', 'abc', 123);
        $this->config_repository->set('test', 'def', 456);

        $this->config_repository->set('test2', 'abc', 123);
        $this->config_repository->set('test2', 'def', 456);

        $this->assertSame(123, ConfigFacade::get('test', 'abc'));
        $this->assertSame(456, ConfigFacade::get('test', 'def'));

        $this->assertSame(123, ConfigFacade::get('test2', 'abc'));
        $this->assertSame(456, ConfigFacade::get('test2', 'def'));
    }

    #[Test]
    public function test_if_throws_ConfigKeyMissingException_if_config_key_does_not_exists(): void {
        $this->config_repository->set('test', 'abc', 123);
        $this->assertSame(123, ConfigFacade::get('test', 'abc'));
        
        $this->expectException(ConfigKeyMissingException::class);
        ConfigFacade::get('test', 'def');        
    }

    #[Test]
    public function test_can_set_config(): void {
        ConfigFacade::set('test', 'abc', 123);
        ConfigFacade::set('test', 'def', 456);

        ConfigFacade::set('test2', 'abc', 123);
        ConfigFacade::set('test2', 'def', 456);

        $this->assertSame(123, ConfigFacade::get('test', 'abc'));
        $this->assertSame(456, ConfigFacade::get('test', 'def'));

        $this->assertSame(123, ConfigFacade::get('test2', 'abc'));
        $this->assertSame(456, ConfigFacade::get('test2', 'def'));
    }

    #[Test]
    public function test_it_can_check_if_config_file_has_config_key(): void {
        ConfigFacade::set('test', 'abc', 123);
        
        $this->assertTrue(ConfigFacade::has('test', 'abc'));
        $this->assertFalse(ConfigFacade::has('test', 'def'));
    }

    #[Test]
    public function test_when_checking_if_config_file_has_config_key_it_throws_if_file_do_not_exists(): void {
        $this->expectException(ConfigFileMissingException::class);
        $this->assertTrue(ConfigFacade::has('test', 'abc'));
    }

    #[Test]
    public function test_it_can_check_if_config_file_exists(): void {
        ConfigFacade::set('test', 'abc', 123);

        $this->assertTrue(ConfigFacade::fileExists('test'));
        $this->assertFalse(ConfigFacade::fileExists('test2'));
    }

    #[Test]
    public function test_it_can_get_config_config_providing_fallback(): void {
        ConfigFacade::set('test', 'abc', 123);

        $this->assertSame(123, ConfigFacade::getOr('test', 'abc', 456));
    }

    #[Test]
    public function test_it_returns_the_provided_fallback_if_config_key_does_not_exists(): void {
        ConfigFacade::set('test', 'abc', 123);
        $this->assertSame(456, ConfigFacade::getOr('test', 'def', 456));
        $this->assertNull( ConfigFacade::getOr('test', 'def'));
    }

    #[Test]
    public function test_it_returns_the_provided_fallback_if_config_file_does_not_exists(): void {
        $this->assertSame(456, ConfigFacade::getOr('test', 'abc', 456));
        $this->assertNull(ConfigFacade::getOr('test', 'abc'));
    }

    #[Test]
    public function test_it_throws_InvalidArgumentException_if_passed_config_key_is_empty_string_while_getting_config_with_fallback(): void {
        $this->expectException(InvalidArgumentException::class);
        ConfigFacade::getOr('test', '');
    }

    #[Test]
    public function test_it_throws_InvalidArgumentException_if_passed_config_file_is_empty_string_while_getting_config_with_fallback(): void {
        $this->expectException(InvalidArgumentException::class);
        ConfigFacade::getOr('', 'abc');
    }
    
}
