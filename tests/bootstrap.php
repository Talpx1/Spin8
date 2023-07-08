<?php

defined('ABSPATH') || $_ENV["TESTING"] || die('[Spin8 - TESTING] Direct access is not allowed!');

$autoloader = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloader) && is_readable($autoloader))
    require_once $autoloader;
else
    throw new RuntimeException("[Spin8] Can't require the autoloader in ".basename(__FILE__).", it's either missing or non-readable. Check the autoloader in {$autoloader}");

require_once __DIR__ . '/../src/bootstrap.php';
