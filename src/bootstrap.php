<?php
use Spin8\Configs\ConfigRepository;
use Spin8\Spin8;
// ##### TEMPLATE ENGINE #####
/**
 * @var Latte\Engine
 */
$latte = new Latte\Engine();
$latte->setTempDirectory(__DIR__ . "/temp/latte");

require_once(__DIR__ . "/functions.php");

$spin8 = Spin8::instance();

/**
 * @var \Spin8\Configs\ConfigRepository
 */
$config_repository = $spin8->singletone(ConfigRepository::class);

$config_repository->loadAll();
