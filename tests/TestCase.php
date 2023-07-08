<?php

namespace Spin8\Tests;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDO;
use Brain\Monkey;

class TestCase extends \PHPUnit\Framework\TestCase {

    use MockeryPHPUnitIntegration;

    /**
     * Faker instance
     *
     * @var \Faker\Generator|null
     */
    protected static $faker;

    /**
     * DB connection variable
     *
     * @var PDO|null
     */
    protected static $db;


    public static function setUpBeforeClass(): void {
        self::$db = new PDO('sqlite::memory:');
        self::$faker = \Faker\Factory::create();
    }

    public static function tearDownAfterClass(): void {
        self::$db = null;
        self::$faker = null;
    }

    public function setUp(): void {
        parent::setUp();
        
        self::$db->query(
            "PRAGMA writable_schema = 1;
            DELETE FROM sqlite_master WHERE TYPE IN ('table', 'index', 'trigger');
            PRAGMA writable_schema = 0;
            VACUUM;
            PRAGMA INTEGRITY_CHECK;"
        );
    }

    public function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }
}
