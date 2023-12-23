<?php

declare(strict_types=1);

namespace Spin8\Facades;

/**
 * @mixin \Spin8\Configs\ConfigRepository
 *
 * @method static mixed get(string $file_name, string $config_key)
 * @method static void set(string $file_name, string $config_key, mixed $value)
 * @method static void setFrom(array $configs)
 * @method static bool has(string $file_name, string $config_key)
 * @method static bool fileLoaded(string $file_name)
 * @method static mixed getOr(string $file_name, string $config_key, mixed $default = null)
 * @method static void clear()
 *
 * @see \Spin8\Configs\ConfigRepository
 * @see \Spin8\Facades\Facade
 */
final class Config extends Facade {
    /** @var string[] $allowed */
    protected static array $allowed = ['get', 'set', 'setFrom', 'has', 'fileLoaded', 'getOr', 'clear'];

    protected static function implementor() : string {
        return 'config';
    }
}
