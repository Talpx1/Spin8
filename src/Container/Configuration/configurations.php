<?php declare(strict_types=1);

use Spin8\TemplatingEngine\Engines\LatteEngine;
use Spin8\Configs\ConfigRepository;
use Spin8\Spin8;
use Spin8\TemplatingEngine\TemplatingEngine;

return [
    'aliases' => [
        'config' => ConfigRepository::class,
        'latte' => LatteEngine::class
    ],

    'templating_engines' => [
        'latte' => LatteEngine::class
    ],

    'singletons' => [
        ConfigRepository::class,
    ],

    'entries' => [
        TemplatingEngine::class => LatteEngine::class,
    ]
];