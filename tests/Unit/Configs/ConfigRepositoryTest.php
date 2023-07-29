<?php

namespace Spin8\Tests\Unit\Configs;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Spin8\Configs\ConfigRepository;
use Spin8\Configs\Exceptions\ConfigFileNotLoadedException;
use Spin8\Configs\Exceptions\ConfigFileNotReadableException;
use Spin8\Configs\Exceptions\ConfigKeyMissingException;
use Spin8\Facades\Config;
use Spin8\Spin8;
use Spin8\Tests\TestCase;

#[CoversClass(ConfigRepository::class)]
final class ConfigRepositoryTest extends TestCase {

    #[Test]
    public function test_it_can_discover_config_file(): void {
        $config_file_path = $this->makeConfigFile("test")->url();

        $reflected_class = new ReflectionClass(ConfigRepository::class);
        /** @var ConfigRepository */
        $reflected_instance = $reflected_class->newInstanceWithoutConstructor();
        $reflected_class->getMethod('discoverFiles')->invoke($reflected_instance);

        /** @var string[] */
        $config_files_found = $reflected_class->getProperty('config_files')->getValue($reflected_instance);

        $this->assertIsArray($config_files_found);
        $this->assertNotEmpty($config_files_found);
        $this->assertContains($config_file_path, $config_files_found);
        $this->assertCount(1, $config_files_found);
    }

    #[Test]
    public function test_it_can_load_config_file(): void {
        $config_file_path = $this->makeConfigFile("test_cfg", ['test' => 123, 'test2' => "hello"])->url();

        $reflected_class = new ReflectionClass(ConfigRepository::class);
        /** @var ConfigRepository */
        $reflected_instance = $reflected_class->newInstanceWithoutConstructor();
        $reflected_class->getMethod('loadFile')->invoke($reflected_instance, $config_file_path);

        /** @var array<string, array<string, mixed>> */
        $configs_loaded = $reflected_class->getProperty('configs')->getValue($reflected_instance);

        $this->assertIsArray($configs_loaded);
        $this->assertNotEmpty($configs_loaded);
        $this->assertCount(1, $configs_loaded);
        $this->assertArrayHasKey('test_cfg', $configs_loaded);
        $this->assertArrayHasKey('test', $configs_loaded['test_cfg']);
        $this->assertArrayHasKey('test2', $configs_loaded['test_cfg']);
        $this->assertSame(123, $configs_loaded['test_cfg']['test']);
        $this->assertSame('hello', $configs_loaded['test_cfg']['test2']);

        // @phpstan-ignore-next-line
        container()->singleton(ConfigRepository::class, $reflected_instance);

        $this->assertSame(123, Config::get('test_cfg', 'test'));
        $this->assertSame('hello', Config::get('test_cfg', 'test2'));        
    }

    #[Test]
    public function test_it_throws_ConfigFileNotReadableException_if_config_file_is_not_readable(): void {
        $config_file_path = $this->makeConfigFile("test_cfg", ['test' => 123, 'test2' => "hello"], 000)->url();

        $reflected_class = new ReflectionClass(ConfigRepository::class);
        /** @var ConfigRepository */
        $reflected_instance = $reflected_class->newInstanceWithoutConstructor();
        $this->expectException(ConfigFileNotReadableException::class);
        $reflected_class->getMethod('loadFile')->invoke($reflected_instance, $config_file_path);
    }

    #[Test]
    public function test_it_can_set_a_config(): void {
        $reflected_class = new ReflectionClass(ConfigRepository::class);
        /** @var ConfigRepository */
        $reflected_instance = $reflected_class->newInstanceWithoutConstructor();
        $reflected_class->getMethod('set')->invoke($reflected_instance, 'test_file', 'test_config', 123);

        /** @var array<string, array<string, mixed>> */
        $configs_loaded = $reflected_class->getProperty('configs')->getValue($reflected_instance);
        $this->assertNotEmpty($configs_loaded);
        $this->assertArrayHasKey('test_file', $configs_loaded);
        $this->assertArrayHasKey('test_config', $configs_loaded['test_file']);
        $this->assertSame(123, $configs_loaded['test_file']['test_config']);
        
        // @phpstan-ignore-next-line
        container()->singleton(ConfigRepository::class, $reflected_instance);
        
        $this->assertSame(123, Config::get('test_file', 'test_config'));        
    }

    #[Test]
    public function test_it_throws_InvalidArgumentException_when_setting_a_config_if_file_name_is_an_empty_string(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->configRepository()->set('', 'test_config', 123);
    }

    #[Test]
    public function test_it_throws_InvalidArgumentException_when_setting_a_config_if_config_key_is_an_empty_string(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->configRepository()->set('test_file', '', 123);
    }

    #[Test]
    public function test_it_can_provide_all_configs(): void {
        $this->configRepository()->set('test1', 'a', 1);
        $this->configRepository()->set('test2', 'b', 2);
        $this->configRepository()->set('test3', 'c', 3);
        $this->configRepository()->set('test4', 'd', 4);
        $this->configRepository()->set('test5', 'e', 5);
        $this->configRepository()->set('test6', 'f', 6);

        $all_configs = $this->configRepository()->getAll();

        $this->assertIsArray($all_configs);
        $this->assertNotEmpty($all_configs);
        $this->assertCount(6, $all_configs);

        $this->assertArrayHasKey("test1", $all_configs);
        $this->assertArrayHasKey("test2", $all_configs);
        $this->assertArrayHasKey("test3", $all_configs);
        $this->assertArrayHasKey("test4", $all_configs);
        $this->assertArrayHasKey("test5", $all_configs);
        $this->assertArrayHasKey("test6", $all_configs);

        $this->assertArrayHasKey("a", $all_configs['test1']);
        $this->assertArrayHasKey("b", $all_configs['test2']);
        $this->assertArrayHasKey("c", $all_configs['test3']);
        $this->assertArrayHasKey("d", $all_configs['test4']);
        $this->assertArrayHasKey("e", $all_configs['test5']);
        $this->assertArrayHasKey("f", $all_configs['test6']);

        $this->assertSame(1, $all_configs['test1']["a"]);
        $this->assertSame(2, $all_configs['test2']["b"]);
        $this->assertSame(3, $all_configs['test3']["c"]);
        $this->assertSame(4, $all_configs['test4']["d"]);
        $this->assertSame(5, $all_configs['test5']["e"]);
        $this->assertSame(6, $all_configs['test6']["f"]);
    }

    #[Test]
    public function test_it_can_clear_all_configs(): void {
        $this->generateRandomConfigs(100);
        
        $this->assertNotEmpty($this->configRepository()->getAll());
        $this->assertCount(100, $this->configRepository()->getAll());

        $this->configRepository()->clear();

        $this->assertEmpty($this->configRepository()->getAll());
    }

    #[Test]
    public function test_it_can_load_all_configs(): void {
        $config_file_1 = $this->makeConfigFile('test1', ['abc' => 123])->url();
        $config_file_2 = $this->makeConfigFile('test2', ['def' => 456])->url();
        $config_file_3 = $this->makeConfigFile('test3', ['ghi' => 789])->url();
        $config_file_4 = $this->makeConfigFile('test4', ['jkl' => 987])->url();
        $config_file_5 = $this->makeConfigFile('test5', ['mno' => 654])->url();
        $config_file_6 = $this->makeConfigFile('test6', ['pqr' => 321])->url();

        $reflected_class = new ReflectionClass(ConfigRepository::class);
        /** @var ConfigRepository */
        $reflected_instance = $reflected_class->newInstanceWithoutConstructor();
        /** @var array<string, array<string, mixed>> */
        $configs_loaded_before = $reflected_class->getProperty('configs')->getValue($reflected_instance);
        $files_loaded_before = $reflected_class->getProperty('config_files')->getValue($reflected_instance);
        
        $this->assertEmpty($configs_loaded_before);
        $this->assertEmpty($files_loaded_before);

        $reflected_instance->loadAll();

        /** @var array<string, array<string, mixed>> */
        $configs_loaded_after = $reflected_class->getProperty('configs')->getValue($reflected_instance);
        $files_loaded_after = $reflected_class->getProperty('config_files')->getValue($reflected_instance);

        $this->assertIsArray($files_loaded_after);
        $this->assertNotEmpty($files_loaded_after);
        $this->assertCount(6, $files_loaded_after);

        $this->assertContains($config_file_1, $files_loaded_after);
        $this->assertContains($config_file_2, $files_loaded_after);
        $this->assertContains($config_file_3, $files_loaded_after);
        $this->assertContains($config_file_4, $files_loaded_after);
        $this->assertContains($config_file_5, $files_loaded_after);
        $this->assertContains($config_file_6, $files_loaded_after);

        $this->assertIsArray($configs_loaded_after);
        $this->assertNotEmpty($configs_loaded_after);
        $this->assertCount(6, $configs_loaded_after);

        $this->assertArrayHasKey('test1', $configs_loaded_after);
        $this->assertArrayHasKey('test2', $configs_loaded_after);
        $this->assertArrayHasKey('test3', $configs_loaded_after);
        $this->assertArrayHasKey('test4', $configs_loaded_after);
        $this->assertArrayHasKey('test5', $configs_loaded_after);
        $this->assertArrayHasKey('test6', $configs_loaded_after);

        $this->assertArrayHasKey('abc', $configs_loaded_after['test1']);
        $this->assertArrayHasKey('def', $configs_loaded_after['test2']);
        $this->assertArrayHasKey('ghi', $configs_loaded_after['test3']);
        $this->assertArrayHasKey('jkl', $configs_loaded_after['test4']);
        $this->assertArrayHasKey('mno', $configs_loaded_after['test5']);
        $this->assertArrayHasKey('pqr', $configs_loaded_after['test6']);

        $this->assertSame(123, $configs_loaded_after['test1']['abc']);
        $this->assertSame(456, $configs_loaded_after['test2']['def']);
        $this->assertSame(789, $configs_loaded_after['test3']['ghi']);
        $this->assertSame(987, $configs_loaded_after['test4']['jkl']);
        $this->assertSame(654, $configs_loaded_after['test5']['mno']);
        $this->assertSame(321, $configs_loaded_after['test6']['pqr']);
    }

    #[Test]
    public function test_it_can_provide_a_config(): void {
        $this->configRepository()->set('test1', 'a', 1);
        $this->configRepository()->set('test2', 'b', 2);

        $config_1 = $this->configRepository()->get("test1", 'a');
        $config_2 = $this->configRepository()->get("test2", 'b');

        $this->assertSame(1, $config_1);
        $this->assertSame(2, $config_2);
    }

    #[Test]
    public function test_it_throws_ConfigKeyMissingException_when_getting_a_config_if_config_key_does_not_exists_in_specified_file(): void {
        $this->configRepository()->set('test1', 'a', 1);

        $this->expectException(ConfigKeyMissingException::class);

        $this->configRepository()->get("test1", 'b');
    }

    #[Test]
    public function test_it_throws_InvalidArgumentException_when_getting_a_config_if_file_name_is_an_empty_string(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->configRepository()->get('', 'test_config');
    }

    #[Test]
    public function test_it_throws_InvalidArgumentException_when_getting_a_config_if_config_key_is_an_empty_string(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->configRepository()->get('test_file', '');
    }

    #[Test]
    public function test_it_can_check_if_a_file_has_a_config(): void {
        $this->configRepository()->set('test1', 'a', 1);

        $result_1 = $this->configRepository()->has("test1", 'a');
        $result_2 = $this->configRepository()->has("test1", 'b');

        $this->assertTrue($result_1);
        $this->assertFalse($result_2);
    }

    #[Test]
    public function test_it_throws_ConfigFileNotLoadedException_when_checking_if_a_config_has_a_config_key_if_the_given_file_has_not_loaded(): void {
        $this->expectException(ConfigFileNotLoadedException::class);

        $this->configRepository()->has("test1", 'a');
    }

    #[Test]
    public function test_it_throws_InvalidArgumentException_when_checking_if_a_config_has_a_config_key_if_file_name_is_an_empty_string(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->configRepository()->has('', 'test_config');
    }

    #[Test]
    public function test_it_throws_InvalidArgumentException_when_checking_if_a_config_has_a_config_key_if_config_key_is_an_empty_string(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->configRepository()->has('test_file', '');
    }

    #[Test]
    public function test_it_throws_InvalidArgumentException_when_checking_if_a_config_file_has_loaded_if_file_name_is_an_empty_string(): void {
        $this->expectException(InvalidArgumentException::class);

        $this->configRepository()->fileLoaded("");
    }

    #[Test]
    public function test_it_can_check_if_a_file_has_loaded(): void {
        $this->configRepository()->set('test1', 'a', 1);

        $result_1 = $this->configRepository()->fileLoaded("test1");
        $result_2 = $this->configRepository()->fileLoaded("test2");

        $this->assertTrue($result_1);
        $this->assertFalse($result_2);
    }

    #[Test]
    public function test_it_throws_InvalidArgumentException_when_getting_a_config_with_fallback_if_file_name_is_an_empty_string(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->configRepository()->getOr('', 'test_config', 'test');
    }

    #[Test]
    public function test_it_throws_InvalidArgumentException_when_getting_a_config_with_fallback_if_config_key_is_an_empty_string(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->configRepository()->getOr('test_file', '', 'test');
    }

    #[Test]
    public function test_it_can_get_a_config_and_fallback_if_config_is_not_found(): void {
        $this->configRepository()->set('test1', 'a', 1);

        $this->assertSame(1, $this->configRepository()->getOr('test1', 'a', 2));
        $this->assertNull($this->configRepository()->getOr('test1', 'b'));
        $this->assertNull($this->configRepository()->getOr('test2', 'b'));
        $this->assertSame('a', $this->configRepository()->getOr('test2', 'b', 'a'));
        $this->assertSame('a', $this->configRepository()->getOr('test1', 'b', 'a'));
    }
}
