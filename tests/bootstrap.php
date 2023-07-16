<?php

$autoloader = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloader) && is_readable($autoloader)) {
    require_once $autoloader;
} else {
    throw new RuntimeException("[Spin8] Can't require the autoloader in ".basename(__FILE__).", it's either missing or non-readable. Check the autoloader in {$autoloader}");
}

//WP_Mock::activateStrictMode();
WP_Mock::setUsePatchwork(FALSE); //not using patchwork because it breaks phpunit :(
WP_Mock::bootstrap();

require_once __DIR__ . '/../src/bootstrap.php';