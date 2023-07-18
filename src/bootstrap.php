<?php declare(strict_types=1);

use Spin8\Configs\ConfigRepository;
use Spin8\Spin8;

// ##### TEMPLATE ENGINE #####
/**
 * @var Latte\Engine
 */
$latte = new Latte\Engine();
$latte->setTempDirectory(__DIR__ . "/../../../../../storage/framework/temp/latte/");

$spin8 = Spin8::instance();

require_once(__DIR__ . "/functions.php");

/**
 * @var \Spin8\Configs\ConfigRepository
 */
$config_repository = $spin8->singletone(ConfigRepository::class);

$config_repository->loadAll();
