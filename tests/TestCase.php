<?php declare(strict_types=1);

namespace Spin8\Tests;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use Spin8\Configs\ConfigRepository;
use Spin8\Console\Command;
use Spin8\Container\Configuration\ContainerConfigurator;
use Spin8\Container\Container;
use Spin8\Facades\Config;
use Spin8\Spin8;
use Spin8\Guards\GuardAgainstEmptyParameter;
use Spin8\Support\Path;
use Spin8\TemplatingEngine\Engines\BasicEngine;
use Spin8\TemplatingEngine\Engines\LatteEngine;
use Spin8\TemplatingEngine\TemplatingEngine;
use Spin8\WP\Plugin;
use WP_Mock;

//not extending WP_Mock test case because its incompatible with phpunit 10

/** @phpstan-import-type ContainerConfiguration from \Spin8\Container\Configuration\AbstractContainerConfigurator */
class TestCase extends \PHPUnit\Framework\TestCase {

    use MockeryPHPUnitIntegration;

    // Faker instance
    protected \Faker\Generator $faker;

    // Virtual filesystem root
    protected vfsStreamDirectory $filesystem_root;
    protected vfsStreamDirectory $config_path;
    protected vfsStreamDirectory $storage_path;
    protected vfsStreamDirectory $vendor_path;
    protected vfsStreamDirectory $assets_path;
    protected vfsStreamDirectory $plugin_path;
    
    // Framework
    protected Spin8 $spin8;
    protected Plugin $plugin;

    /** @var array<string, mixed> $spin8_configs */
    protected array $spin8_configs = [];




    public function setUp(): void {
        parent::setUp();
        
        $this->faker = \Faker\Factory::create();
        
        $this->createVirtualFileSystem();

        $this->setUpFramework();
        
        Mockery::close();
        WP_Mock::setUp();
    }

    public function tearDown(): void {
        unset($this->faker);
        
        Config::clear();

        $this->spin8->container->clear();

        Spin8::dispose();

        unset($this->spin8);
        
        WP_Mock::tearDown();
        
        Mockery::close();
        
        parent::tearDown();
    }

    protected function createVirtualFileSystem(): void {
        $this->filesystem_root = vfsStream::setup(structure:[
            "assets" => ["admin" => [], "common" => [], "public"=>[]],
            "configs" => [],
            "plugin" => [],
            "storage" => ["framework" => ["temp" => []]],
            "vendor" => ["talp1" => ["spin8" => ["framework" => ["src" => []]]]],
        ]);
        

        // @phpstan-ignore-next-line
        $this->config_path = $this->filesystem_root->getChild('configs');
        // @phpstan-ignore-next-line
        $this->storage_path = $this->filesystem_root->getChild('storage');
        // @phpstan-ignore-next-line
        $this->vendor_path = $this->filesystem_root->getChild('vendor');
        // @phpstan-ignore-next-line
        $this->plugin_path = $this->filesystem_root->getChild('plugin');
        // @phpstan-ignore-next-line
        $this->assets_path = $this->filesystem_root->getChild('assets');
    }

    /**
     * creates a new config file with the given name in the appropriate virtual directory.
     *
     * @param string $file_name the name that will be given to the newly created file.
     * @param array<string, mixed> $configs, configs to write in that file.
     */
    public function makeConfigFile(string $file_name, array $configs = [], int $permissions = null): vfsStreamFile {
        GuardAgainstEmptyParameter::check($file_name);

        return vfsStream::newFile("{$file_name}.php", $permissions)
            ->withContent("<?php return ".var_export($configs, true).";")
            ->at($this->config_path);
    }
    
    protected function setUpFramework(): void {
        $container = new Container();
        
        $container_configurator = new ContainerConfigurator($this->configureContainer());
        
        $container->useConfigurator($container_configurator);

        $spin8_configs = array_merge([
            'project_root_path' => $this->filesystem_root->url(),
            'templating_engine' => new BasicEngine()
        ], $this->spin8_configs);

        $this->spin8 = Spin8::init($container, $spin8_configs);

        require_once(__DIR__ . "/../src/functions.php");

        $this->plugin = $container->singleton(Plugin::class);
        $container->alias('plugin', Plugin::class);
    }

    /**
     * generates a given amount of random configs
     *
     * @param int $amount amount of config to generate
     */
    public function generateRandomConfigs(int $amount): void {
        for($i = 0; $i < $amount; $i++){
            Config::set(
                $this->faker->unique()->slug(),
                $this->faker->unique()->word(),
                $this->faker->randomFloat(),
            );
        }
    }

    /** @return ContainerConfiguration */
    protected function configureContainer(): array {
        return [
            'entries' => [
                TemplatingEngine::class => LatteEngine::class,
            ],

            'templating_engines' => [
                'latte' => LatteEngine::class
            ],

            'singletons' => [
                ConfigRepository::class,
            ],

            'aliases' => [
                'config' => ConfigRepository::class,
                'support.path' => Path::class,
            ]
        ];
    }

    public function configRepository(): ConfigRepository {
        return $this->spin8->container->get(ConfigRepository::class);
    }
    
    /**
     * @param class-string<Command> $class
     */
    public function getHelpMessageForCommand(string $class): string {
        \Safe\ob_start();
        (new $class([],[]))->showHelp();
        $output = ob_get_clean();

        if($output === false) {
            throw new \RuntimeException("Unable to retrieve help message for command {$class}");
        }
        
        return $output;
    }

    /**
     * @return array<array{string[]|array{}, string[]|array{}}>
     */
    public static function help_flags_and_args_provider() : array {
        return [
            [["--help"], []],
            [["-help"], []],
            [[], ["help"]]
        ];
    }

    /**
     * @param class-string $class
     * @param non-empty-string[] $methods
     * @param array<non-empty-string, mixed> $constructor_args
     */
    public function partialMockWithConstructorArgs(string $class, array $methods, array $constructor_args): \PHPUnit\Framework\MockObject\MockObject {
        return $this->getMockBuilder($class)
            ->setConstructorArgs($constructor_args)
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->onlyMethods($methods)
            ->getMock();
    }
}
