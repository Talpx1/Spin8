<?php

declare(strict_types=1);

namespace Spin8\Facades;

/**
 * @mixin \Spin8\Configs\ConfigRepository
 *
 * @method static mixed get(string $key)
 * @method static void set(string $key, mixed $value)
 * @method static void setFrom(array $configs)
 * @method static bool has(string $key)
 * @method static mixed getOr(string $key, mixed $default = null)
 * @method static void clear()
 *
 * @see \Spin8\Configs\ConfigRepository
 * @see \Spin8\Facades\Facade
 */
final class Config extends Facade {
    /** @var string[] */
    protected static array $allowed = ['get', 'set', 'setFrom', 'has', 'getOr', 'clear'];

    protected static function implementor() : string {
        return 'config';
    }
}
