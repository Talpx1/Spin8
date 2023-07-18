<?php

namespace Spin8\Tests\Unit;

use Closure;
use Error;
use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Spin8\Configs\ConfigRepository;
use Spin8\Configs\Exceptions\ConfigFileNotReadableException;
use Spin8\Configs\Facades\ConfigFacade;
use Spin8\Spin8;
use Spin8\Tests\TestCase;
use WP_Mock;

#[CoversClass(ConfigRepository::class)]
final class ConfigRepositoryTest extends TestCase {

    #[Test]
    public function test_config_repository_cant_be_instantiated(): void {
        $this->expectException(Error::class);
        // @phpstan-ignore-next-line
        new ConfigRepository();
    }

    #[Test]
    public function test_config_repository_is_a_singletone_and_provide_only_one_instance(): void {
        $first_instance = ConfigRepository::instance();
        $second_instance = ConfigRepository::instance();

        $this->assertSame($first_instance, $second_instance);
    }

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

        Spin8::instance()->replaceSingletone(ConfigRepository::class, $reflected_instance);

        $this->assertSame(123, ConfigFacade::get('test_cfg', 'test'));
        $this->assertSame('hello', ConfigFacade::get('test_cfg', 'test2'));        
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
        
        $this->spin8->replaceSingletone(ConfigRepository::class, $reflected_instance);
        
        $this->assertSame(123, ConfigFacade::get('test_file', 'test_config'));        
    }

    #[Test]
    public function test_it_throws_InvalidArgumentException_if_file_name_is_an_empty_string(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->config_repository->set('', 'test_config', 123);
    }

    #[Test]
    public function test_it_throws_InvalidArgumentException_if_config_key_is_an_empty_string(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->config_repository->set('test_file', '', 123);
    }

    #[Test]
    public function test_it_can_provide_all_configs(): void {
        $this->config_repository->set('test1', 'a', 1);
        $this->config_repository->set('test2', 'b', 2);
        $this->config_repository->set('test3', 'c', 3);
        $this->config_repository->set('test4', 'd', 4);
        $this->config_repository->set('test5', 'e', 5);
        $this->config_repository->set('test6', 'f', 6);

        $all_configs = $this->config_repository->getAll();

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
        
        $this->assertNotEmpty($this->config_repository->getAll());
        $this->assertCount(100, $this->config_repository->getAll());

        $this->config_repository->clear();

        $this->assertEmpty($this->config_repository->getAll());
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
}
