<?php

namespace Spin8\Tests;

use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PDO;
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
     * DB connection variable
     */
    protected PDO $db;

    /**
     * Virtual filesystem root
     */
    public readonly vfsStreamDirectory $filesystem_root;


    // public static function setUpBeforeClass(): void {
        
    // }

    // public static function tearDownAfterClass(): void {
        
    // }

    public function setUp(): void {
        parent::setUp();        
        
        $this->faker = \Faker\Factory::create();
        $this->db = new PDO('sqlite::memory:');
        
        $this->createVirtualFileSystem();
        
        Mockery::close();
        WP_Mock::setUp();        
    }

    public function tearDown(): void {      
        unset($this->faker);
        unset($this->db);        
        WP_Mock::tearDown();
        Mockery::close();
        parent::tearDown();
    }

    protected function clearMemoryDb(): void{        
        $this->db->query(
            "PRAGMA writable_schema = 1;
            DELETE FROM sqlite_master WHERE TYPE IN ('table', 'index', 'trigger');
            PRAGMA writable_schema = 0;
            VACUUM;
            PRAGMA INTEGRITY_CHECK;"
        );        

        unset($this->db);
    }

    protected function createVirtualFileSystem(): void{
        $this->filesystem_root = vfsStream::setup(structure:[
            "configs" => [],
            "storage" => ["framework" => ["temp"=>[]]],
            "vendor" => ["talp1" => ["spin8" => ["framework" => ["src" => []]]]],
        ]);
    }

    /**
     * removes the unnecessary part of virtual filesystem paths to simulate that these are actually on-disk paths
     */
    public function vfsPathToRealPath(string $vfs_path): string{
        GuardAgainstEmptyParameter::check($vfs_path);

        if(!str_starts_with($vfs_path, "vfs://root")) {
            throw new InvalidArgumentException("\$vfs_path needs to be a virtual filesystem path, but {$vfs_path} is not. Thrown in ".__METHOD__);
        }

        $path = str_replace("vfs://root", '', $vfs_path);
        if(! str_ends_with($path, "/")) $path .= '/';

        return $path;
    }


    /**
     * removes the unnecessary part of paths to simulate that the root path is actually '/' and not the result of adjusting the output of __DIR__
     */
    public function removeLocalPath(string $real_path): string{
        GuardAgainstEmptyParameter::check($real_path);

        return str_replace(dirname(__DIR__)."/src/../../../../..", "", $real_path);
    }
}
