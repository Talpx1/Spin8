<?php

namespace Spin8\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversFunction;

use Spin8\Configs\Enums\Environments;
use Spin8\Configs\Facades\ConfigFacade;
use Spin8\Tests\TestCase;


#[CoversFunction("admin_asset")]
#[CoversFunction("slugify")]
#[CoversFunction("build_settings")]
#[CoversFunction("config")]
#[CoversFunction("isRunningTest")]
#[CoversFunction("root_path")]
#[CoversFunction("assets_path")]
#[CoversFunction("framework_path")]
#[CoversFunction("framework_src_path")]
#[CoversFunction("config_path")]
#[CoversFunction("storage_path")]
#[CoversFunction("framework_temp_path")]
#[CoversFunction("environment")]
final class FunctionsTest extends TestCase {

    public function test_environment_helper_returns_right_environment(): void {
        $this->assertTrue(array_key_exists("TESTING", $_ENV) && $_ENV['TESTING'] = '1');
        $this->assertTrue(isRunningTest());

        $this->assertSame(Environments::TESTING, environment());

        unset($_ENV['TESTING']);
        $this->assertFalse(isRunningTest());
        $this->assertFalse(array_key_exists("TESTING", $_ENV) && $_ENV['TESTING'] = '1');

        ConfigFacade::set('environment', 'environment', Environments::PRODUCTION);
        $this->assertSame(config('environment', 'environment'), environment());
        $this->assertSame(Environments::PRODUCTION, environment());

        ConfigFacade::set('environment', 'environment', Environments::LOCAL);
        $this->assertSame(Environments::LOCAL, environment());
    }

}