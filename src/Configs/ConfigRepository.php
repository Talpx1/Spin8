<?php declare(strict_types=1);

namespace Spin8\Configs;
use InvalidArgumentException;
use Spin8\Configs\Exceptions\ConfigFileNotReadableException;
use Spin8\Spin8;

/**
 * @method array getAll()
 */
class ConfigRepository{
    protected array $config_files = [];
    protected array $configs = [];

    public static ?self $instance = null;

    
    public static function instance(): self{
        if(is_null(self::$instance)) self::$instance = new self();
        
        return self::$instance;
    }

    protected function __construct(){}

    public function loadAll(): void {
        $this->discoverFiles();
        foreach($this->config_files as $config_file) $this->loadFile($config_file);
    }

    public function getAll(): array {
        return $this->configs;
    }

    public function setConfig(string $file_name, string $config_key, mixed $value): void {
        if(empty($file_name)) throw new InvalidArgumentException("\$file_name cannot be empty in ".__METHOD__);
        if(empty($config_key)) throw new InvalidArgumentException("\$config_key cannot be empty in ".__METHOD__);

        $this->configs[$file_name][$config_key] = $value;
    }

    protected function loadFile(string $config_file): void {
        if (!is_readable($config_file)) throw new ConfigFileNotReadableException($config_file);

        $config_file_name = pathinfo($config_file, PATHINFO_FILENAME);
        $this->configs[$config_file_name] = [];

        $configs = require $config_file;
        
        foreach($configs as $config_key => $config_value) $this->configs[$config_file_name][$config_key] = $config_value;
    }

    protected function discoverFiles(): void {
        $this->config_files = glob(configPath()."*.php");
    }

    public static function __callStatic(string $method, array $args) {
        if($method === "all") return Spin8::instance()->singletone(self::class)->getAll();
        if($method === "set") call_user_func_array([Spin8::instance()->singletone(self::class), "setConfig"], $args);
    }

}
