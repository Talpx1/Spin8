<?php declare(strict_types=1);

namespace Spin8\Configs;
use Spin8\Configs\Exceptions\ConfigFileNotReadableException;
use Spin8\Utils\Guards\GuardAgainstEmptyParameter;

class ConfigRepository{
    /**
     * @var string[]
     */
    protected array $config_files = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $configs = [];

    protected static ?self $instance = null;

    
    public static function instance(): self {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    protected function __construct(){}
    
    public function loadAll(): void {
        $this->discoverFiles();
        
        foreach($this->config_files as $config_file) {
            $this->loadFile($config_file);
        }
    }
    
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

    public function set(string $file_name, string $config_key, mixed $value): void {
        GuardAgainstEmptyParameter::check($file_name);
        GuardAgainstEmptyParameter::check($config_key);

        $this->configs[$file_name][$config_key] = $value;
    }

    //TODO: set from array method

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

}
