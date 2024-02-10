<?php

namespace Spin8\Tests\Unit\WP;

use org\bovigo\vfs\vfsStream;
use RuntimeException;
use Spin8\Facades\Config;
use Spin8\WP\Plugin;
use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Tests\TestCase;
use WP_Mock;

#[CoversClass(Plugin::class)]
class PluginTest extends TestCase {

    #[Test]
    public function test_activation_hooks_gets_registered(): void {
        $plugin = new Plugin();

        WP_Mock::userFunction('register_activation_hook')->once()->with(pluginFilePath(), [$plugin, 'onActivation']);
        WP_Mock::userFunction('register_deactivation_hook');

        $plugin->registerLifecycleHooks();
    }

    #[Test]
    public function test_deactivation_hooks_gets_registered(): void {
        $plugin = new Plugin();

        WP_Mock::userFunction('register_deactivation_hook')->once()->with(pluginFilePath(), [$plugin, 'onDeactivation']);
        WP_Mock::userFunction('register_activation_hook');

        $plugin->registerLifecycleHooks();
    }

    #[Test]
    public function test_user_cant_activate_plugin_without_the_proper_capability(): void {
        $plugin = new Plugin();

        WP_Mock::userFunction('current_user_can')->once()->with('activate_plugins')->andReturn(false);        

        $plugin->activation();
    }

    #[Test]
    public function test_it_throws_RuntimeException_on_activation_if_installed_php_version_is_older_than_min_php_version_set_in_configs(): void {
        $plugin = new Plugin();
        Config::set('environment', 'min_php_version', "999");
        
        WP_Mock::userFunction('current_user_can')->andReturn(true);
        
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('In order to run this plugin, PHP version %s (or higher) is required. Your current PHP version is %s. Please update PHP.', "999", PHP_VERSION));
        $plugin->activation();
    }
    
    #[Test]
    public function test_it_throws_RuntimeException_on_activation_if_installed_wp_version_is_older_than_min_php_version_set_in_configs(): void {
        $plugin = new Plugin();
        Config::set('environment', 'min_php_version', "1");
        Config::set('environment', 'min_wordpress_version', "999");

        WP_Mock::userFunction('current_user_can')->andReturn(true);
        WP_Mock::userFunction('get_bloginfo')->andReturn("1");

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('In order to run this plugin, WordPress version %s (or higher) is required. Your current WordPress version is %s. Please update WordPress.', "999", wpVersion()));
        $plugin->activation();
    }

    #[Test]
    public function test_it_loads_user_defined_menus_on_activation(): void {
        Config::set('environment', 'min_php_version', "1");
        Config::set('environment', 'min_wordpress_version', "1");

        WP_Mock::userFunction('current_user_can')->andReturn(true);
        WP_Mock::userFunction('get_bloginfo')->andReturn("999");
        
        vfsStream::newFile('menus.php')->at($this->plugin_path)->setContent('menu');

        \Safe\ob_start();
        (new Plugin())->activation();
        $content = ob_get_clean();

        $this->assertEquals('menu', $content);
    }

    #[Test]
    public function test_it_loads_user_defined_settings_on_activation(): void {
        Config::set('environment', 'min_php_version', "1");
        Config::set('environment', 'min_wordpress_version', "1");

        WP_Mock::userFunction('current_user_can')->andReturn(true);
        WP_Mock::userFunction('get_bloginfo')->andReturn("999");
        
        vfsStream::newFile('settings.php')->at($this->plugin_path)->setContent('settings');

        \Safe\ob_start();
        (new Plugin())->activation();
        $content = ob_get_clean();

        $this->assertEquals('settings', $content);
    }

    #[Test]
    public function test_it_loads_user_defined_activation_hook_on_activation(): void {
        Config::set('environment', 'min_php_version', "1");
        Config::set('environment', 'min_wordpress_version', "1");

        WP_Mock::userFunction('current_user_can')->andReturn(true);
        WP_Mock::userFunction('get_bloginfo')->andReturn("999");
        
        vfsStream::newFile('activation.php')->at($this->plugin_path)->setContent('activation');

        \Safe\ob_start();
        (new Plugin())->activation();
        $content = ob_get_clean();

        $this->assertEquals('activation', $content);
    }

    #[Test]
    public function test_user_cant_deactivate_plugin_without_the_proper_capability(): void {
        $plugin = new Plugin();

        WP_Mock::userFunction('current_user_can')->once()->with('activate_plugins')->andReturn(false);        

        $plugin->deactivation();
    }

    #[Test]
    public function test_it_loads_user_defined_deactivation_hook_on_deactivation(): void {
        WP_Mock::userFunction('current_user_can')->andReturn(true);        
        
        vfsStream::newFile('deactivation.php')->at($this->plugin_path)->setContent('deactivation');

        \Safe\ob_start();
        (new Plugin())->deactivation();
        $content = ob_get_clean();

        $this->assertEquals('deactivation', $content);
    }
}
