<?php

namespace Spin8\Tests\Unit;

use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Configs\Enums\Environments;
use Spin8\Configs\Facades\ConfigFacade;
use Spin8\Tests\TestCase;
use WP_Mock;


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
        $vfs_path = $this->vfsPathToRealPath(vfsStream::url("root/vendor/talp1/spin8/framework/src/"));
        $real_path = $this->removeLocalPath(frameworkSrcPath());
        
        $this->assertSame($vfs_path, $real_path);
        $this->assertSame("/vendor/talp1/spin8/framework/src/", $real_path);
    }

    #[Test]
    public function test_frameworkPath_helper_points_to_right_directory(): void {        
        $vfs_path = $this->vfsPathToRealPath(vfsStream::url("root/vendor/talp1/spin8/framework/"));
        $real_path = $this->removeLocalPath(frameworkPath());
        
        $this->assertSame($vfs_path, $real_path);
        $this->assertSame("/vendor/talp1/spin8/framework/", $real_path);
    }

    #[Test]
    public function test_assetsPath_helper_points_to_right_directory(): void {        
        $vfs_path = $this->vfsPathToRealPath(vfsStream::url("root/assets/"));
        $real_path = $this->removeLocalPath(assetsPath());
        
        $this->assertSame($vfs_path, $real_path);
        $this->assertSame("/assets/", $real_path);
    }

    #[Test]
    public function test_rootPath_helper_points_to_right_directory(): void {        
        $vfs_path = $this->vfsPathToRealPath(vfsStream::url("root"));
        $real_path = $this->removeLocalPath(rootPath());
        
        $this->assertSame($vfs_path, $real_path);
        $this->assertSame("/", $real_path);
    }

    #[Test]
    public function test_isRunningTest_helper_returns_true_when_running_test(): void {        
        //$_ENV['TESTING'] = '1' gets set by PHPUnit
        $this->assertTrue(isRunningTest());
    }

    #[Test]
    public function test_isRunningTest_helper_returns_false_when_not_running_test(): void {        
        //$_ENV['TESTING'] = '1' gets set by PHPUnit
        //unsetting $_ENV['TESTING'] to simulate a non testing environment
        unset($_ENV['TESTING']);
        $this->assertFalse(isRunningTest());

        //changing $_ENV['TESTING'] to a value different than '1', should not be considered a testing environment
        $_ENV['TESTING'] = 'test';
        $this->assertFalse(isRunningTest());
    }

    #[Test]
    public function test_config_helper_returns_specified_configuration(): void {        
        ConfigFacade::set('test', 'cfg_test', '123');

        $this->assertSame('123', config('test', 'cfg_test'));
    }

    #[Test]
    public function test_config_helper_returns_specified_fallback_if_configuration_key_cant_be_found(): void {        
        //no configs exists right now, so every config we try to fetch is going to fallback
        $this->assertSame('fallback', config('test', 'cfg_test', 'fallback'));
    }

    #[Test]
    public function test_config_helper_returns_null_if_configuration_key_cant_be_found_and_no_fallback_is_specified(): void {        
        //no configs exists right now, so every config we try to fetch is going to fallback
        $this->assertNull(config('test', 'cfg_test'));
    }

    #[Test]
    public function test_config_helper_throws_InvalidArgumentException_if_file_name_is_an_empty_string(): void {  
        $this->expectException(InvalidArgumentException::class);      
        config('', 'cfg_test');
    }

    #[Test]
    public function test_config_helper_throws_InvalidArgumentException_if_config_key_is_an_empty_string(): void {  
        $this->expectException(InvalidArgumentException::class);      
        config('test', '');
    }

    #[Test]
    public function test_buildSettings_helper_renders_form(): void { 
        $fake_plugin_name = 'test_plugin';
        $fake_page_slug = 'test_page_slug';

        ConfigFacade::set('plugin', 'name', $fake_plugin_name);

        WP_Mock::userFunction('settings_errors')->once()->with("{$fake_plugin_name}_message");
        WP_Mock::userFunction('settings_fields')->once()->with($fake_page_slug);
        WP_Mock::userFunction('do_settings_sections')->once()->with($fake_page_slug);
        WP_Mock::userFunction('__')->once()->with('Save')->andReturn('Save');
        WP_Mock::userFunction('submit_button')->once()->with('Save');

        $this->assertStringContainsString("<form action='options.php' method='post'>", buildSettings($fake_page_slug));
    }

}
