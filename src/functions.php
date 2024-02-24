<?php declare(strict_types=1);

use Spin8\Configs\Enums\Environments;
use Spin8\Container\Interfaces\Spin8ContainerContract;
use Spin8\Exceptions\EnvironmentVariableNotFoundException;
use Spin8\Facades\Config;
use Spin8\Spin8;
use Spin8\Guards\GuardAgainstEmptyParameter;
use Spin8\Facades\Path;
use Spin8\WP\Plugin;

/**
 * Returns the container instance
 */
function container(): Spin8ContainerContract {
    return spin8()->container;
}

/**
 * Returns the instance of Spin8 from the container
 */
function spin8(): Spin8 {
    return Spin8::instance();
}

/**
 * Returns plugin's current instance.
 */
function plugin(): Plugin {
    return container()->get('plugin');
}

/**
 * Render an asset located in assets/admin/.
 *
 * @param string $path path of the assets inside assets/admin/. Do not specify the file extension.
 * @param array<string, mixed> $data data in key=>value format to pass to the template. Passed data is available in the template using $key.
 *
 * @throws InvalidArgumentException
 */
function adminAsset(string $path, array $data = []): void {
    GuardAgainstEmptyParameter::check($path);

    $path = ltrim($path, DIRECTORY_SEPARATOR);

    $path = assetsPath("admin/{$path}") . "." . spin8()->templating_engine->extension;
    
    spin8()->templating_engine->render($path, $data);
}

/**
 * Tries to create a valid slug starting from a string.
 * This function makes use of WordPress's sanitize_title function.
 * @see https://developer.wordpress.org/reference/functions/sanitize_title/
 *
 * @param string $string the string to convert in slug.
 *
 * @throws InvalidArgumentException
 */
function slugify(string $string): string {
    GuardAgainstEmptyParameter::check($string);
    
    $slug = remove_accents($string);
    $slug = sanitize_title_with_dashes($slug, "", 'save');
    $slug = str_replace(['_', ' '], '-', $slug);

    if (empty($slug)) {
        throw new RuntimeException(sprintf(__("An error occurred while converting a string to a slug. Passed string: %s"), $string));
    }
    
    return $slug;
}

/**
 * Provide the settings form used in WordPress settings pages, in string form, will need to be outputted.
 * This function is intended to be used in templates.
 *
 * @param string $page_slug slug of the setting page, available in Latte page templates via the $page_slug variable.
 * @param ?string $submit_text text to use for the 'submit'/'save' button.
 * 
 * @return string returns the HTML form (and eventual errors) to be printed in the template.
 *
 * @throws InvalidArgumentException
 *
 * @see https://developer.wordpress.org/reference/functions/add_settings_error/
 * @see https://developer.wordpress.org/reference/functions/settings_errors/
 * @see https://developer.wordpress.org/reference/functions/settings_fields/
 * @see https://developer.wordpress.org/reference/functions/do_settings_sections/
 * @see https://developer.wordpress.org/reference/functions/submit_button/
 */
function buildSettings(string $page_slug, string $submit_text = null): string {
    GuardAgainstEmptyParameter::check($page_slug);
    GuardAgainstEmptyParameter::check($submit_text, allow_null: true);
    
    if(!is_null($submit_text)) {
        $submit_text = sanitize_text_field($submit_text);
    }
    
    if (isset($_GET['settings-updated'])) {
        add_settings_error(config('plugin', 'name') . '-messages', config('plugin', 'name') . '_message', __('Settings Saved'), 'updated');
    }

    Safe\ob_start();
    settings_errors(config('plugin', 'name') . '_message');
    $buffered_settings_errors = ob_get_clean();

    if($buffered_settings_errors === false) {
        throw new RuntimeException("An error occurred while building {$page_slug} settings page");
    }

    Safe\ob_start();
    settings_fields($page_slug);
    do_settings_sections($page_slug);
    submit_button($submit_text ?? __('Save'));
    $buffer_settings = ob_get_clean();

    if($buffer_settings === false) {
        throw new RuntimeException("An error occurred while building {$page_slug} settings page");
    }

    return "{$buffered_settings_errors}<form action='options.php' method='post'>{$buffer_settings}</form>";
}

/**
 * Retrieve and returns the value of the specified config. If the file or the config key is not found, @var $default is returned.
 *
 * @param string $file_name name, with no extension, of the file in the 'configs' directory that contains the specified config value.
 * @param string $config_key the key of the desired config to retrieve.
 * @param mixed $default the fallback value to return in case the specified config can not be found.
 * @return mixed
 */
function config(string $file_name, string $config_key, mixed $default = null): mixed {
    return Config::getOr($file_name, $config_key, $default);
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
 * The trailing slash is not included, and if provided in {@param $path} it will be removed.
 * 
 * @param string $path if provided, it will be appended.
 *
 * @return string
 */
function rootPath(string $path = ""): string {
    return Path::append(spin8()->project_root_path, $path);
}

/**
 * Returns the assets path of this project.
 * The trailing slash is not included, and if provided in {@param $path} it will be removed.
 * 
 * @param string $path if provided, it will be appended.
 *
 * @return string
 */
function assetsPath(string $path = ""): string {
    return Path::append(rootPath("assets"), $path);
}

/**
 * Returns the Spin8 package framework path (inside vendor/talp1/spin8).
 * The trailing slash is not included, and if provided in {@param $path} it will be removed.
 * 
 * @param string $path if provided, it will be appended.
 *
 * @return string
 */
function frameworkPath(string $path = ""): string {
    return Path::append(rootPath("vendor/spin8/framework"), $path);
}

/**
 * Returns the Spin8 package src path (inside vendor/talp1/spin8/framework).
 * The trailing slash is not included, and if provided in {@param $path} it will be removed.
 * 
 * @param string $path if provided, it will be appended.
 *
 * @return string
 */
function frameworkSrcPath(string $path = ""): string {
    return Path::append(frameworkPath("src"), $path);
}

/**
 * Returns the framework's storage temporary path of this project.
 * The trailing slash is not included, and if provided in {@param $path} it will be removed.
 * 
 * @param string $path if provided, it will be appended.
 *
 * @return string
 */
function frameworkTempPath(string $path = ""): string {
    return Path::append(storagePath("framework/temp"), $path);
}

/**
 * Returns the configs path of this project. Here is where all the config files are stored.
 * The trailing slash is not included, and if provided in {@param $path} it will be removed.
 * 
 * @param string $path if provided, it will be appended.
 *
 * @return string
 */
function configPath(string $path = ""): string {
    return Path::append(rootPath("configs"), $path);
}

/**
 * Returns the storage path of this project.
 * The trailing slash is not included, and if provided in {@param $path} it will be removed.
 * 
 * @param string $path if provided, it will be appended.
 *
 * @return string
 */
function storagePath(string $path = ""): string {
    return Path::append(rootPath("storage"), $path);
}

/**
 * Returns the plugin path of this project.
 * The trailing slash is not included, and if provided in {@param $path} it will be removed.
 * 
 * @param string $path if provided, it will be appended.
 *
 * @return string
 */
function pluginPath(string $path = ""): string {
    return Path::append(rootPath("plugin"), $path);
}
}

/**
 * Returns the path of the plugin main file.
 * 
 * @return string
 */
function pluginFilePath(): string {
    return rootPath(config('plugin', 'slug').".php");
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

/**
 * Returns the value of an env variable.
 * 
 * @param string $name the name of the environment variable to be read.
 *
 * @return mixed
 */
function env(string $name): mixed {
    GuardAgainstEmptyParameter::check($name);

    if(!array_key_exists($name, $_ENV)) {
        throw new EnvironmentVariableNotFoundException("{$name} is not an environment variable. Maybe you forgot to set it in .env, or maybe you wanted to access a config.");
    }

    return $_ENV[$name];
}

/**
 * Returns the value of an env variable if exists, otherwise it fallback to the provided value.
 * 
 * @param string $name the name of the environment variable to be read.
 * @param mixed $default the fallback value to return in case the specified env variable can not be found.
 *
 * @return mixed
 */
function envOr(string $name, mixed $default = null): mixed {
    GuardAgainstEmptyParameter::check($name);

    try {
        return env($name);
    } catch (EnvironmentVariableNotFoundException) {
        return $default;
    }
}

/**
 * Returns the installed version of WordPress.
 * 
 * @return string
 */
function wpVersion(): string {
    return get_bloginfo('version');
}

/**
 * Require a file only if exists.
 * 
 * @param string $path path of the file to require.
 * @param bool $use_require_once if true (default) require_once will be used, require otherwise. 
 */
function requireIfExists(string $path, bool $use_require_once = true): void {
    if(!file_exists($path)) {
        return;
    }

    $use_require_once ? require_once $path : require $path;
}