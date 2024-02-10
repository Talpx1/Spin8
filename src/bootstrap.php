<?php declare(strict_types=1);

use Spin8\Spin8;
use Spin8\Container\Container;
use Spin8\Configs\ConfigRepository;
use Spin8\Container\Configuration\ContainerConfigurator;
use Spin8\WP\Plugin;

// CONTAINER
$container = new Container();

$container_configurator = new ContainerConfigurator(__DIR__."/Container/Configuration/configurations.php");

$container->useConfigurator($container_configurator);


// FRAMEWORK
$spin8 = Spin8::init($container);

require_once __DIR__ . "/functions.php";


// CONFIGS
$container->get(ConfigRepository::class)->loadAll();


// PLUGIN
/** @var Plugin  */
$plugin = $container->singleton(Plugin::class);
$container->alias('plugin', Plugin::class);

$plugin->registerLifecycleHooks();