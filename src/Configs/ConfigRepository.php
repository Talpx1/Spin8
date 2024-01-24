<?php declare(strict_types=1);

namespace Spin8\Configs;

use InvalidArgumentException;
use Spin8\Configs\Exceptions\ConfigFileNotLoadedException;
use Spin8\Configs\Exceptions\ConfigFileNotReadableException;
use Spin8\Configs\Exceptions\ConfigKeyMissingException;
use Spin8\Guards\GuardAgainstEmptyParameter;

class ConfigRepository{
    /**
     * @var string[]
     */
    protected array $config_files = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $configs = [];

    
    public function __construct(){}
    

    public function loadAll(): void {
        $this->discoverFiles();
        
        foreach($this->config_files as $config_file) {
            $this->loadFile($config_file);
        }
    }

    /**
     * clear all the loaded configs
     */
    public function clear(): void {
        $this->config_files = [];
        $this->configs = [];
    }

    
    /**
     * @return array<string, array<string, mixed>>
     */
    public function getAll(): array {
        return $this->configs;
    }


    /**
     * set the specified config to the specified value
     *
     * @param string $file_name file to set the @var $config_key in
     * @param string $config_key key of the config to set
     * @param mixed $value value to assign to the config key
     */
    public function set(string $file_name, string $config_key, mixed $value): void {
        GuardAgainstEmptyParameter::check($file_name);
        GuardAgainstEmptyParameter::check($config_key);

        $this->configs[$file_name][$config_key] = $value;
    }

    /**
     * massively set the specified configs to the specified values
     * 
     * format:
     * ```['file_name' => ['config_key' => value, ...], ...]```
     *
     * @param array<string, array<string, mixed>> $configs to massively set.
     */
    public function setFrom(array $configs): void {
        GuardAgainstEmptyParameter::check($configs);

        foreach($configs as $file_name => $configs_group){
            if(! is_string($file_name)) {
                throw new InvalidArgumentException("{$file_name} is not a valid config file name. It should be string, ".gettype($file_name)." passed.");
            }

            foreach($configs_group as $config_key => $value) {
                if(! is_string($config_key)) {
                    throw new InvalidArgumentException("{$config_key} is not a valid config key. It should be string, ".gettype($file_name)." passed.");
                }

                $this->set($file_name, $config_key, $value);
            }
        }
    }

    protected function loadFile(string $config_file): void {
        if (!is_readable($config_file)) {
            throw new ConfigFileNotReadableException($config_file);
        }
        
        $config_file_name = pathinfo($config_file, PATHINFO_FILENAME);
        $this->configs[$config_file_name] = [];
        
        /**
         * @var array<string, mixed>
         */
        $configs = require $config_file;
        
        /**
         * @var string $config_key
         */
        foreach($configs as $config_key => $config_value) {
            $this->configs[$config_file_name][$config_key] = $config_value;
        }
    }

    /**
     * @internal not using glob, even if far more concise, because it uses the glob:// stream wrapper, making it not testable with the in-memory filesystem
     */
    protected function discoverFiles(): void {
        $dir_content = \Safe\scandir(configPath());
        $config_files = array_filter($dir_content, fn($path) => pathinfo($path, PATHINFO_EXTENSION) === "php");
        $this->config_files = array_map(fn($config_file) => configPath().$config_file, $config_files);
    }

    /**
     * get the specified config
     *
     * @param string $file_name file to retrieve the `@param $config_key` from
     * @param string $config_key key of the config to retrieve
     *
     * @throws ConfigKeyMissingException
     */
    public function get(string $file_name, string $config_key): mixed {
        GuardAgainstEmptyParameter::check($file_name);
        GuardAgainstEmptyParameter::check($config_key);

        if(! $this->has($file_name, $config_key)) {
            throw new ConfigKeyMissingException($config_key, $file_name);
        }
        
        return $this->getAll()[$file_name][$config_key];
    }

    /**
     * check whether the specified config exists in the specified file
     *
     * @param string $file_name file to look into to check if the @var $config_key exists
     * @param string $config_key key of the config to check
     *
     * @throws ConfigFileNotLoadedException
     */
    public function has(string $file_name, string $config_key): bool {
        GuardAgainstEmptyParameter::check($file_name);
        GuardAgainstEmptyParameter::check($config_key);

        if(!$this->fileLoaded($file_name)) {
            throw new ConfigFileNotLoadedException($file_name);
        }
        
        return array_key_exists($config_key, $this->getAll()[$file_name]);
    }

    /**
     * check whether the specified config file has been loaded
     *
     * @param string $file_name file to check
     */
    public function fileLoaded(string $file_name): bool {
        GuardAgainstEmptyParameter::check($file_name);
        
        return array_key_exists($file_name, $this->getAll());
    }

    /**
     * get the specified config or return the provided fallback
     *
     * @param string $file_name file to retrieve the @var $config_key from
     * @param string $config_key key of the config to retrieve
     * @param mixed $default fallback in case the specified config key can't be found in the specified file
     */
    public function getOr(string $file_name, string $config_key, mixed $default = null): mixed {
        GuardAgainstEmptyParameter::check($file_name);
        GuardAgainstEmptyParameter::check($config_key);

        try{
            if($this->has($file_name, $config_key)) {
                return $this->getAll()[$file_name][$config_key];
            }

            return $default;
        } catch(ConfigFileNotLoadedException){
            return $default;
        }
    }

}