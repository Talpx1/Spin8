<?php

defined('ABSPATH') || $_ENV["TESTING"] || die('[JsonLD for WordPress - TESTING] Direct access is not allowed!');

$autoloader = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloader) && is_readable($autoloader))
    require_once $autoloader;
else
    throw new RuntimeException(sprintf(__("[JsonLD for WordPress] Can't require the autoloader in %s, it's either missing or non-readable. Check the autoloader in %s", "jsonld-for-wordpress"), basename(__FILE__), $autoloader));

require_once __DIR__ . '/../vendor/yoast/wp-test-utils/src/BrainMonkey/bootstrap.php';
require_once __DIR__ . '/../framework/bootstrap.php';
