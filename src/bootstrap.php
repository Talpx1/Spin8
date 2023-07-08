<?php
// ##### TEMPLATE ENGINE #####
/**
 * @var Latte\Engine
 */
$latte = new Latte\Engine();
$latte->setTempDirectory(__DIR__ . "/temp/latte");

require_once(__DIR__ . "/functions.php");
