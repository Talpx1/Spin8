<?php

namespace Spin8\Tests;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PDO;
use Brain\Monkey;

class TestCase extends \PHPUnit\Framework\TestCase {

    use MockeryPHPUnitIntegration;

    /**
     * Faker instance
     */
    protected ?\Faker\Generator $faker;

    /**
     * DB connection variable
     */
    protected ?PDO $db;

    /**
     * Virtual filesystem root
     */
    public readonly ?vfsStreamDirectory $filesystem_root;
    public readonly ?vfsStreamDirectory $config_directory;
    public readonly ?vfsStreamDirectory $storage_directory;
    public readonly ?vfsStreamDirectory $framework_storage_directory;
    public readonly ?vfsStreamDirectory $framework_temp_storage_directory;


    // public static function setUpBeforeClass(): void {
        
    // }

    // public static function tearDownAfterClass(): void {
        
    // }

    public function setUp(): void {
        parent::setUp();        
        
        $this->faker = \Faker\Factory::create();
        $this->db = new PDO('sqlite::memory:');

        $this->createVirtualFileSystem();
    }

    public function tearDown(): void {
        Monkey\tearDown();        
        unset($this->faker);
        parent::tearDown();
    }

    protected function clearMemoryDb(){        
        $this->db->query(
            "PRAGMA writable_schema = 1;
            DELETE FROM sqlite_master WHERE TYPE IN ('table', 'index', 'trigger');
            PRAGMA writable_schema = 0;
            VACUUM;
            PRAGMA INTEGRITY_CHECK;"
        );        

        unset($this->db);
    }

    protected function createVirtualFileSystem(){
        $this->filesystem_root = vfsStream::setup('root');
        $this->config_directory = vfsStream::newDirectory("config")->at($this->filesystem_root);
        $this->storage_directory = vfsStream::newDirectory("storage")->at($this->filesystem_root);        
        $this->framework_storage_directory = vfsStream::newDirectory("framework")->at($this->storage_directory);        
        $this->framework_temp_storage_directory = vfsStream::newDirectory("temp")->at($this->framework_storage_directory);        
    }
}
