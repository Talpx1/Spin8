<?php declare(strict_types=1);

namespace Spin8\Configs\Facades;
use InvalidArgumentException;
use Spin8\Configs\ConfigRepository;
use Spin8\Configs\Exceptions\ConfigKeyMissingException;

class ConfigFacade{

    /**
     * get the specified config
     *
     * @param string $file_name file to retrive the @var $config_key from
     * @param string $config_key key of the config to retrive
     * 
     * @throws ConfigKeyMissingException
     */
    public static function get(string $file_name, string $config_key): mixed{
        if(! self::has($file_name, $config_key)) throw new ConfigKeyMissingException($config_key, $file_name);
        return ConfigRepository::all()[$file_name][$config_key];
    }

    /**
     * set the specified config to the specified value
     *
     * @param string $file_name file to set the @var $config_key in
     * @param string $config_key key of the config to set
     * @param mixed $value value to assign to the config key
     */
    public static function set(string $file_name, string $config_key, mixed $value): void{
        ConfigRepository::set($file_name, $config_key, $value);
    }

    /**
     * check wether the specified config exists in the specified file
     *
     * @param string $file_name file to look into to check if the @var $config_key exists
     * @param string $config_key key of the config to check
     */
    public static function has(string $file_name, string $config_key): bool{
        return array_key_exists($file_name, ConfigRepository::all()) && array_key_exists($config_key, ConfigRepository::all()[$file_name]);
    }

    /**
     * get the specified config or return the provided fallback
     *
     * @param string $file_name file to retrive the @var $config_key from
     * @param string $config_key key of the config to retrive
     * @param mixed $default fallback in case the specified config key can't be found in the specified file
     * 
     * @throws InvalidArgumentException
     */
    public static function getOr(string $file_name, string $config_key, mixed $default = null): mixed{
        if(empty($file_name)) throw new InvalidArgumentException("\$file_name cannot be empty in ".__METHOD__);
        if(empty($config_key)) throw new InvalidArgumentException("\$config_key cannot be empty in ".__METHOD__);

        if(self::has($file_name, $config_key)) return ConfigRepository::all()[$file_name][$config_key];
        return $default;
    }
}
