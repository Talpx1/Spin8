<?php declare(strict_types=1);

namespace Spin8\WP;
use RuntimeException;

class Plugin {

    public function registerLifecycleHooks(): void {
        register_activation_hook(pluginFilePath(), [$this, 'activation']);
        register_deactivation_hook(pluginFilePath(), [$this, 'deactivation']);
    }

    public function activation(): void {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        $this->checkEnvironment();

        requireIfExists(pluginPath('menus.php'));
        requireIfExists(pluginPath('settings.php'));
        requireIfExists(pluginPath('activation.php'));

    }

    public function deactivation(): void {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        requireIfExists(pluginPath('deactivation.php'));
    }

    protected function checkEnvironment(): void {
        if (!version_compare(PHP_VERSION, config('environment', 'min_php_version'), '>=')) {
            throw new RuntimeException(sprintf(__('In order to run this plugin, PHP version %s (or higher) is required. Your current PHP version is %s. Please update PHP.'), config('environment', 'min_php_version'), PHP_VERSION));
        }

        if (!version_compare(wpVersion(), config('environment', 'min_wordpress_version'), '>=')) {
            throw new RuntimeException(sprintf(__('In order to run this plugin, WordPress version %s (or higher) is required. Your current WordPress version is %s. Please update WordPress.'), config('environment', 'min_wordpress_version'), wpVersion()));
        }
    }

}