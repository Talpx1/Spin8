<?php

use Spin8\Configs\Enums\Environments;
use Spin8\Configs\Facades\ConfigFacade;

/**
 * Render a Latte asset located in assets/admin.
 *
 * @param string $path path of the assets inside assets/admin.
 * @param array $data data in key=>value format to pass to the Latte template. Passed data is available in the template using $key.
 */
function adminAsset(string $path, array $data = []): void {
    if (empty($path)) throw new RuntimeException(sprintf(__("%s needs a valid path. Empty path passed."), __FUNCTION__));
    global $latte;
    $latte->render(assetsPath() . "/admin/$path.latte", $data);
}

/**
 * Tries to create a valid slug starting from a string.
 * This function makes use of WordPress's sanitize_title function.
 * @see https://developer.wordpress.org/reference/functions/sanitize_title/
 *
 * @param string $string the string to convert in slug.
 */
function slugify(string $string): string {
    if (empty($string)) throw new RuntimeException(sprintf(__("%s needs a valid string. Empty string passed."), __FUNCTION__));
    $slug = strtolower($string);
    $slug = sanitize_title($slug);
    $slug = str_replace(['_', ' '], '-', $slug);
    if (empty($slug)) throw new RuntimeException(sprintf(__("An error occurred while converting a string to a slug. Passed string: %s"), $string));
    return $slug;
}

/**
 * Render the settings form used in Wordpress settings pages.
 * This function is intended to be used in Latte templates.
 *
 * @param string $page_slug slug of the setting page, available in Latte page templates via the $page_slug variable.
 * @param string|null $submit_text text to use for the 'submit'/'save' button.
 *
 * @see https://developer.wordpress.org/reference/functions/add_settings_error/
 * @see https://developer.wordpress.org/reference/functions/settings_errors/
 * @see https://developer.wordpress.org/reference/functions/settings_fields/
 * @see https://developer.wordpress.org/reference/functions/do_settings_sections/
 * @see https://developer.wordpress.org/reference/functions/submit_button/
 */
function buildSettings(string $page_slug, string $submit_text = null): void {
    if (isset($_GET['settings-updated'])) add_settings_error(config('plugin', 'name') . '-messages', config('plugin', 'name') . '_message', __('Settings Saved'), 'updated');
    
    settings_errors(config('plugin', 'name') . '_message');

    ob_start();
    echo '<form action="options.php" method="post">';
    settings_fields($page_slug);
    do_settings_sections($page_slug);
    submit_button($submit_text ?? __('Save'));
    echo '</form>';
    echo ob_get_clean();
}

/**
 * Retrive and returns the value of the specified config. If the file or the config key is not found, @var $default is returned.
 *
 * @param string $file_name name, with no extension, of the file in the 'configs' directory that contains the specified config value.
 * @param string $config_key the key of the desired config to retrive.
 * @param mixed $default the fallback value to return in case the specified config can not be found.
 * @return mixed
 */
function config(string $file_name, string $config_key, mixed $default = null): mixed {
    return ConfigFacade::getOr($file_name, $config_key, $default);
}

/**
 * Returns true if the app is currently running a test, false otherwise
 *
 * @return boolean
 */
function isRunningTest(): bool {
    return array_key_exists("TESTING", $_ENV) && $_ENV['TESTING'] === '1';
}

//PATHS
/**
 * Returns the root path of this project.
 * The trailing slash is included.
 *
 * @return string
 */
function rootPath(): string {    
    return __DIR__ . "/../../../../../";
}

/**
 * Returns the assets path of this project.
 * The trailing slash is included.
 *
 * @return string
 */
function assetsPath(): string {
    return rootPath() . "assets/";
}

/**
 * Returns the Spin8 package framework path (inside vendor/talp1/spin8).
 * The trailing slash is included.
 *
 * @return string
 */
function frameworkPath(): string {
    return rootPath() . "vendor/talp1/spin8/framework/";
}

/**
 * Returns the Spin8 package src path (inside vendor/talp1/spin8/framework).
 * The trailing slash is included.
 *
 * @return string
 */
function frameworkSrcPath(): string {
    return frameworkPath() . "src/";
}


/**
 * Returns the configs path of this project. Here is where all the config files are stored.
 * The trailing slash is included.
 *
 * @return string
 */
function configPath(): string {
    return rootPath() . "configs/";
}

/**
 * Returns the storage path of this project.
 * The trailing slash is included.
 *
 * @return string
 */
function storagePath(): string {
    return rootPath() . "storage/";
}

/**
 * Returns the framework's storage temporary path of this project.
 * The trailing slash is included.
 *
 * @return string
 */
function frameworkTempPath(): string {
    return storagePath() . "framework/temp/";
}

/**
 * Returns currently set environment.
 * Automatically detects if the app is running a test.
 * Should be preferred over getting the environment from config.
 *
 * @return \Spin8\Configs\Enums\Environments
 */
function environment(): Environments {
    return isRunningTest() ? Environments::TESTING : config('environment', 'environment');
}