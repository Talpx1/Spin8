<?php

namespace Spin8\Tests;

use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use org\bovigo\vfs\vfsStreamWrapper;
use PDO;
use Spin8\Configs\ConfigRepository;
use Spin8\Spin8;
use Spin8\Utils\Guards\GuardAgainstEmptyParameter;
use WP_Mock;

//not extending WP_Mock test case because its incompatible with phpunit 10
class TestCase extends \PHPUnit\Framework\TestCase {

    use MockeryPHPUnitIntegration;

    /**
     * Faker instance
     */
    protected \Faker\Generator $faker;

    /**
     * Virtual filesystem root
     */
    protected vfsStreamDirectory $filesystem_root;
    protected vfsStreamDirectory $config_path;
    protected vfsStreamDirectory $storage_path;
    protected vfsStreamDirectory $vendor_path;
    
    protected ConfigRepository $config_repository;
    
    protected Spin8 $spin8;


    // public static function setUpBeforeClass(): void {
        
    // }

    // public static function tearDownAfterClass(): void {
        
    // }

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
        WP_Mock::tearDown();
        Mockery::close();
        parent::tearDown();

        $this->config_repository->clear();
    }

    protected function createVirtualFileSystem(): void {
        $this->filesystem_root = vfsStream::setup(structure:[
            "configs" => [],
            "storage" => ["framework" => ["temp" => []]],
            "vendor" => ["talp1" => ["spin8" => ["framework" => ["src" => []]]]],
        ]);
        

        // @phpstan-ignore-next-line
        $this->config_path = $this->filesystem_root->getChild('configs');
        // @phpstan-ignore-next-line
        $this->storage_path = $this->filesystem_root->getChild('storage');
        // @phpstan-ignore-next-line
        $this->vendor_path = $this->filesystem_root->getChild('vendor');
    }

    /**
     * removes the unnecessary part of virtual filesystem paths to simulate that these are actually on-disk paths
     */
    public function vfsPathToRealPath(string $vfs_path): string {
        GuardAgainstEmptyParameter::check($vfs_path);

        if(!str_starts_with($vfs_path, "vfs://root")) {
            throw new InvalidArgumentException("\$vfs_path needs to be a virtual filesystem path, but {$vfs_path} is not. Thrown in ".__METHOD__);
        }

        $path = str_replace("vfs://root", '', $vfs_path);
        
        if(! str_ends_with($path, "/")) {
            $path .= '/';
        }

        return $path;
    }


    /**
     * removes the unnecessary part of paths to simulate that the root path is actually '/' and not the result of adjusting the output of __DIR__
     */
    public function removeLocalPath(string $real_path): string {
        GuardAgainstEmptyParameter::check($real_path);

        return str_replace(dirname(__DIR__)."/src/../../../../..", "", $real_path);
    }

    /**
     * creates a new config file with the given name in the appropriate virtual directory.
     *
     * @param string $file_name the name that will be given to the newly created file.
     * @param array<string, mixed> $configs, configs to write in that file.
     */
    public function makeConfigFile(string $file_name, array $configs = [], int $permissions = null): vfsStreamFile {
        GuardAgainstEmptyParameter::check($file_name);

        return vfsStream::newFile("{$file_name}.php", $permissions)->withContent("<?php return ".var_export($configs, true).";")->at($this->config_path);
    }
    
    protected function setUpFramework(): void {

        $this->spin8 = Spin8::instance()->configure([
            'project_root_path' => $this->filesystem_root->url()
        ]);

        require_once(__DIR__ . "/../src/functions.php");

        $this->config_repository = $this->spin8->singletone(ConfigRepository::class);

        $this->config_repository->loadAll();
    }

    /**
     * generates a given amount of random configs
     *
     * @param int $amount amount of config to generate
     */
    public function generateRandomConfigs(int $amount): void {
        for($i = 0; $i < $amount; $i++){
            $this->config_repository->set(
                $this->faker->unique()->slug(),
                $this->faker->unique()->word(),
                $this->faker->randomFloat(),
            );
        }
    }
}
