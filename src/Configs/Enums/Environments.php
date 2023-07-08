<?php declare(strict_types=1);

namespace JsonLDForWP\Framework\Configs\Enums;

enum Environments{
    case TESTING;
    case LOCAL;
    case STAGING;
    case PRODUCTION;
}