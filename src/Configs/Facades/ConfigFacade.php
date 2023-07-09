<?php declare(strict_types=1);

namespace Spin8\Configs\Facades;
use InvalidArgumentException;
use Spin8\Configs\ConfigRepository;
use Spin8\Configs\Exceptions\ConfigKeyMissingException;

class ConfigFacade{

    public static function get(string $file_name, string $config_key): mixed{
        if(! self::has($file_name, $config_key)) throw new ConfigKeyMissingException($config_key, $file_name);
        return ConfigRepository::all()[$file_name][$config_key];
    }

    public static function set(string $file_name, string $config_key, mixed $value): void{
        ConfigRepository::set($file_name, $config_key, $value);
    }

    public static function has(string $file_name, string $config_key): bool{
        return array_key_exists($file_name, ConfigRepository::all()) && array_key_exists($config_key, ConfigRepository::all()[$file_name]);
    }

    public static function getOr(string $file_name, string $config_key, mixed $default = null): mixed{
        if(empty($file_name)) throw new InvalidArgumentException("\$file_name cannot be empty in ".__METHOD__);
        if(empty($config_key)) throw new InvalidArgumentException("\$config_key cannot be empty in ".__METHOD__);

        if(self::has($file_name, $config_key)) return ConfigRepository::all()[$file_name][$config_key];
        return $default;
    }
}
