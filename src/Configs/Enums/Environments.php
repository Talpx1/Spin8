<?php declare(strict_types=1);

namespace Spin8\Configs\Enums;

enum Environments{
    case TESTING;
    case LOCAL;
    case STAGING;
    case PRODUCTION;
}