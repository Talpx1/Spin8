<?php

namespace Spin8\Tests\Unit;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversFunction;

use PHPUnit\Framework\Attributes\Test;
use Spin8\Configs\Enums\Environments;
use Spin8\Configs\Facades\ConfigFacade;
use Spin8\Tests\TestCase;


#[CoversFunction("adminAsset")]
#[CoversFunction("slugify")]
#[CoversFunction("buildSettings")]
#[CoversFunction("config")]
#[CoversFunction("isRunningTest")]
#[CoversFunction("rootPath")]
#[CoversFunction("assetsPath")]
#[CoversFunction("frameworkPath")]
#[CoversFunction("frameworkSrcPath")]
#[CoversFunction("configPath")]
#[CoversFunction("storagePath")]
#[CoversFunction("frameworkTempPath")]
#[CoversFunction("environment")]
final class FunctionsTest extends TestCase {

    #[Test]
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

    #[Test]
    public function test_frameworkTempPath_helper_points_to_right_directory(): void {        
        $vfs_path = $this->vfsPathToRealPath(vfsStream::url("root/storage/framework/temp/"));
        $real_path = $this->removeLocalPath(frameworkTempPath());
        
        $this->assertSame($vfs_path, $real_path);
        $this->assertSame("/storage/framework/temp/", $real_path);
    }

    #[Test]
    public function test_storagePath_helper_points_to_right_directory(): void {        
        $vfs_path = $this->vfsPathToRealPath(vfsStream::url("root/storage/"));
        $real_path = $this->removeLocalPath(storagePath());
        
        $this->assertSame($vfs_path, $real_path);
        $this->assertSame("/storage/", $real_path);
    }

    #[Test]
    public function test_configPath_helper_points_to_right_directory(): void {        
        $vfs_path = $this->vfsPathToRealPath(vfsStream::url("root/configs/"));
        $real_path = $this->removeLocalPath(configPath());
        
        $this->assertSame($vfs_path, $real_path);
        $this->assertSame("/configs/", $real_path);
    }

    #[Test]
    public function test_frameworkSrcPath_helper_points_to_right_directory(): void {        
        $vfs_path = $this->vfsPathToRealPath(vfsStream::url("root/vendor/talp1/spin8/src/"));
        $real_path = $this->removeLocalPath(frameworkSrcPath());
        
        $this->assertSame($vfs_path, $real_path);
        $this->assertSame("/vendor/talp1/spin8/src/", $real_path);
    }

    //TODO
}