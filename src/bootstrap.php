<?php
// ##### TEMPLATE ENGINE #####
/**
 * @var Latte\Engine
 */
$latte = new Latte\Engine();
$latte->setTempDirectory(__DIR__ . "/temp/latte");

// ##### FAKER #####
/**
 * @var Faker\Generator
 */
$faker = \Faker\Factory::create();

require_once(__DIR__ . "/functions.php");
