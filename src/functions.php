<?php

use Spin8\Configs\Enums\Environments;

/**
 * Render a Latte asset located in assets/admin.
 *
 * @param string $path path of the assets inside assets/admin.
 * @param array $data data in key=>value format to pass to the Latte template. Passed data is available in the template using $key.
 * @return void
 */
function admin_asset(string $path, array $data = []) {
    if (empty($path)) throw new RuntimeException(sprintf(__("%s needs a valid path. Empty path passed."), __FUNCTION__));
    global $latte;
    $latte->render(assets_path() . "/admin/$path.latte", $data);
}

/**
 * Tries to create a valid slug starting from a string.
 * This function makes use of WordPress's sanitize_title function.
 * @see https://developer.wordpress.org/reference/functions/sanitize_title/
 *
 * @param string $string the string to convert in slug.
 * @return string
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
 * This function ss intended to be used in Latte templates.
 *
 * @param string $page_slug slug of the setting page, available in Latte page templates via the $page_slug variable.
 * @param string|null $submit_text text to use for the 'submit'/'save' button.
 * @return void
 *
 * @see https://developer.wordpress.org/reference/functions/add_settings_error/
 * @see https://developer.wordpress.org/reference/functions/settings_errors/
 * @see https://developer.wordpress.org/reference/functions/settings_fields/
 * @see https://developer.wordpress.org/reference/functions/do_settings_sections/
 * @see https://developer.wordpress.org/reference/functions/submit_button/
 */
function build_settings(string $page_slug, string $submit_text = null): void {
    if (isset($_GET['settings-updated'])) add_settings_error(config('plugin', 'name') . '-messages', config('plugin', 'name') . '_message', __('Settings Saved'), 'updated');
    settings_errors(config('plugin', 'name') . '_message');
    echo '<form action="options.php" method="post">';
    settings_fields($page_slug);
    do_settings_sections($page_slug);
    submit_button($submit_text ?? __('Save'));
    echo '</form>';
}

/**
 * Retrive and returns the value of the specified config. If the file or the config key is not found, an exception is thrown.
 *
 * @param string $file_name name, with no extension, of the file in the 'configs' directory that contains the specified config value.
 * @param string $config_key the key of the desired config to retrive.
 * @return mixed
 */
function config(string $file_name, string $config_key): mixed {
    $config_file = config_path() . "$file_name.php";
    if (!file_exists($config_file) || !is_readable($config_file)) throw new RuntimeException(sprintf(__("Unable to retrive %s in configs. The file is either missing or non readable. Check the file in %s."), $file_name, $config_file));
    $configs = require $config_file;
    if (!array_key_exists($config_key, $configs)) throw new RuntimeException(sprintf(__("Unable to retrive %s in %s config file. There's no key named %s."), $config_key, $file_name, $config_key));
    return $configs[$config_key];
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
function root_path(): string {
    return __DIR__ . "/../";
}

/**
 * Returns the assets path of this project.
 * The trailing slash is included.
 *
 * @return string
 */
function assets_path(): string {
    return root_path() . "assets/";
}

/**
 * Returns the framework path of this project.
 * The trailing slash is included.
 *
 * @return string
 */
function framework_path(): string {
    return root_path() . "framework/";
}

/**
 * Returns the configs path of this project. Here is where all the config files are stored.
 * The trailing slash is included.
 *
 * @return string
 */
function config_path(): string {
    return root_path() . "configs/";
}

/**
 * Returns the framework's temporary path of this project.
 * The trailing slash is included.
 *
 * @return string
 */
function framework_temp_path(): string {
    return framework_path() . "temp/";
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