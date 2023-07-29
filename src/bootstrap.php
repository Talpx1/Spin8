<?php declare(strict_types=1);

use Spin8\Spin8;
use Spin8\Container\Container;
use Spin8\Configs\ConfigRepository;
use Spin8\Container\Configuration\ContainerConfigurator;

//AUTOLOADER
$autoloader = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoloader) || !is_readable($autoloader)) {
    throw new RuntimeException("[Spin8] Can't require the autoloader in ".basename(__FILE__).", it's either missing or non-readable. Check the autoloader in {$autoloader}");
}

require_once $autoloader;


// CONTAINER
$container = new Container(require_once __DIR__."/Container/configurations.php");

ContainerConfigurator::run($container);

// FRAMEWORK
$spin8 = Spin8::init($container);

require_once(__DIR__ . "/functions.php");


// CONFIGS
$container->get(ConfigRepository::class)->loadAll();
