<?php

namespace Spin8\Tests;

use InvalidArgumentException;
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
        $this->filesystem_root = vfsStream::setup(structure:[
            "configs" => [],
            "storage" => ["framework" => ["temp"=>[]]],
            "vendor" => ["talp1" => ["spin8" => ["framework" => ["src" => []]]]],
        ]);
    }

    public function vfsPathToRealPath(string $vfs_path){
        if(empty($vfs_path)) throw new InvalidArgumentException("\$vfs_path can not be empty in ".__METHOD__);
        if(!str_starts_with($vfs_path, "vfs://root")) throw new InvalidArgumentException("\$vfs_path needs to be a virtual filesystem path, but {$vfs_path} is not. Thrown in ".__METHOD__);

        $path = str_replace("vfs://root", '', $vfs_path);
        if(! str_ends_with($path, "/")) $path .= '/';

        return $path;
    }

    public function removeLocalPath(string $real_path){
        if(empty($real_path)) throw new InvalidArgumentException("\$real_path can not be empty in ".__METHOD__);

        return str_replace(dirname(__DIR__)."/src/../../../../..", "", $real_path);
    }
}
