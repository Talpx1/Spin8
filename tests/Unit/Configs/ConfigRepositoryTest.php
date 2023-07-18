<?php

namespace Spin8\Tests\Unit;

use Closure;
use Error;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Spin8\Configs\ConfigRepository;
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

}
