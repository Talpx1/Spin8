<?php

namespace Tests;

use PDO;
use Yoast\WPTestUtils\BrainMonkey\TestCase as BrainMonkeyTestCase;

class TestCase extends BrainMonkeyTestCase {

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
        parent::set_up();
        
        self::$db->query(
            "PRAGMA writable_schema = 1;
            DELETE FROM sqlite_master WHERE TYPE IN ('table', 'index', 'trigger');
            PRAGMA writable_schema = 0;
            VACUUM;
            PRAGMA INTEGRITY_CHECK;"
        );
    }

    public function tearDown(): void {
        parent::tear_down();
    }
}
