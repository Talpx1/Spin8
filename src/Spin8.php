<?php declare(strict_types=1);

namespace Spin8;

use Spin8\Container\Interfaces\Spin8ContainerContract;
use Spin8\Exceptions\InvalidConfigurationException;
use Spin8\TemplatingEngine\TemplatingEngine;

final class Spin8{

    private static ?self $instance = null;

    // @phpstan-ignore-next-line
    public readonly string $project_root_path;
    
    // @phpstan-ignore-next-line
    public readonly TemplatingEngine $templating_engine;

    public readonly Spin8ContainerContract $container;

    private const REAL_ROOT_PATH = __DIR__ . "/../../../..";

    public const VERSION = 0.1;

    /** @var array<string, mixed> $default_configurations */
    private array $default_configurations = [
        'project_root_path' => self::REAL_ROOT_PATH,
        'templating_engine' => 'latte'
    ];

    /** @param array<string, mixed> $configurations */
    public static function init(Spin8ContainerContract $container, array $configurations = []): self {
        if(!is_null(self::$instance)) {
            throw new \RuntimeException("Tried to initialize Spin8 when it was already initialized. Use Spin8::instance in order to get the Spin8 instance.");
        }
        self::$instance = new self($container, $configurations);
        
        return self::$instance;
    }

    public static function dispose(): void {
        if(is_null(self::$instance)) {
            throw new \RuntimeException("Tried to dispose Spin8 when it was not yet initialized");
        }
        
        self::$instance = null;
        
    }


    public static function instance(): self {
        if(is_null(self::$instance)) {
            throw new \RuntimeException("Tried to get Spin8 instance before initialization. Initialize Spin8 using 'Spin8::init' method.");
        }

        return self::$instance;
    }


    /** @param array<string, mixed> $configurations */
    private function __construct(Spin8ContainerContract $container, array $configurations) {
        $this->container = $container;

        $configurations = $this->addDefaultConfigurations($configurations);

        $this->configure($configurations);
    }


    /**
     * @param array<string, mixed> $configurations
     * @return array<string, mixed>
     */
    private function addDefaultConfigurations(array $configurations): array {
        foreach($this->default_configurations as $key => $value) {
            if(array_key_exists($key, $configurations)) {
                continue;
            }

            if($key === "templating_engine") {
                $value = $this->container->get($value);
            }

            $configurations[$key] = $value;
        }

        return $configurations;
    }


    private function setProjectRootPath(string $project_root_path): void {
        if(str_ends_with($project_root_path, DIRECTORY_SEPARATOR)) {
            $project_root_path .= substr($project_root_path, 0, -1);
        }

        // @phpstan-ignore-next-line
        $this->project_root_path = $project_root_path;
    }


    private function setTemplatingEngine(TemplatingEngine $templating_engine): void {
        // @phpstan-ignore-next-line
        $this->templating_engine = $templating_engine;

        $this->templating_engine->setTempPath(
            "{$this->project_root_path}storage/framework/temp/templating_engine/{$this->templating_engine->name}"
        );
    }


    /**
     * Allows to set multiple configurations, instead of calling multiple setters, by passing an associative array with the following structure `configuration_key => configuration_value`
     *
     * @param array<string, mixed> $configurations
     *
     * @throws InvalidConfigurationException
     */
    private function configure(array $configurations): self {
        foreach($configurations as $configuration_name => $configuration_value){
            $method = "set".implode(array_map("ucfirst", explode("_", $configuration_name)));
            
            if(! method_exists($this, $method)) {
                throw new InvalidConfigurationException($configuration_name);
            }

            $this->{$method}($configuration_value);
        }

        return $this;
    }
    
}