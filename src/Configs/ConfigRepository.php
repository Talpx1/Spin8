<?php declare(strict_types=1);

namespace Spin8\Configs;

use InvalidArgumentException;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
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
     * @param string $key key of the config to set
     * @param mixed $value value to assign to the config key
     */
    public function set(string $key, mixed $value): void {
        GuardAgainstEmptyParameter::check($key);

        $this->configs[$key] = $value;
    }

    /**
     * massively set the specified configs to the specified values
     * 
     * format:
     * ```['file.config.key' => 'value', ...]```
     *
     * @param array<string, mixed> $configs to massively set.
     */
    public function setFrom(array $configs): void {
        GuardAgainstEmptyParameter::check($configs);

        foreach($configs as $key => $value){
            if(! is_string($key)) {
                throw new InvalidArgumentException("{$key} is not a valid config file name. It should be string, ".gettype($key)." passed.");
            }

            $this->set($key, $value);
        }
    }

    protected function loadFile(string $config_file): void {
        if (!is_readable($config_file)) {
            throw new ConfigFileNotReadableException($config_file);
        }
        
        $config_file_name = pathinfo($config_file, PATHINFO_FILENAME);
        
        /**
         * @var array<string, mixed>
         */
        $configs = require $config_file;
        
        /**
         * @var string $config_key
         */
        $recursive_iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($configs));
        foreach ($recursive_iterator as $leaf) {
            $keys = [$config_file_name];
            foreach (range(0, $recursive_iterator->getDepth()) as $depth) {
                $keys[] = $recursive_iterator->getSubIterator($depth)->key();
            }
            $this->configs[ implode('.', $keys) ] = $leaf;
        }        
    }

    /**
     * @internal not using glob, even if far more concise, because it uses the glob:// stream wrapper, making it not testable with the in-memory filesystem
     */
    protected function discoverFiles(): void {
        $dir_content = \Safe\scandir(configPath());
        $config_files = array_filter($dir_content, fn($path) => pathinfo($path, PATHINFO_EXTENSION) === "php");
        $this->config_files = array_map(fn($config_file) => configPath($config_file), $config_files);
    }

    /**
     * get the specified config
     *
     * @param string $key key of the config to retrieve
     *
     * @throws ConfigKeyMissingException
     */
    public function get(string $key): mixed {
        GuardAgainstEmptyParameter::check($key);

        if(! $this->has($key)) {
            throw new ConfigKeyMissingException($key);
        }
        
        return $this->configs[$key];
    }

    /**
     * check whether the specified config has been loaded
     *
     * @param string $key key of the config to check
     *
     * @throws ConfigFileNotLoadedException
     */
    public function has(string $key): bool {
        GuardAgainstEmptyParameter::check($key);
        
        return array_key_exists($key, $this->configs);
    }

    /**
     * get the specified config or return the provided fallback
     *
     * @param string $key key of the config to retrieve
     * @param mixed $default fallback in case the specified config key can't be found in the specified file
     */
    public function getOr(string $key, mixed $default = null): mixed {
        GuardAgainstEmptyParameter::check($key);

        if($this->has($key)) {
            return $this->configs[$key];
        }

        return $default;
    }

}