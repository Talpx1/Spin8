<?php declare(strict_types=1);

namespace Spin8;
use RuntimeException;
use Spin8\Exceptions\InvalidConfigurationException;

final class Spin8{

    /**
     * @var array<class-string, object>
     */
    private array $singletones = [];

    private static ?self $instance = null;

    private string $project_root_path;
    
    private function __construct() {
        $this->bootstrapWithDefaultValues();
    }
    
    public static function instance(): self {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param class-string $class
     */
    public function singletone(string $class): mixed {
        if(! array_key_exists($class, $this->singletones)) {
            $this->singletones[$class] = $class::instance();
        }

        return $this->singletones[$class];
    }

    private function bootstrapWithDefaultValues(): void {
        $this->project_root_path = __DIR__ . "/../../../../../";
    }

    public function getProjectRootPath(): string {
        return $this->project_root_path;
    }

    public function setProjectRootPath(string $project_root_path): void {
        if(!str_ends_with($project_root_path, DIRECTORY_SEPARATOR)) {
            $project_root_path .= DIRECTORY_SEPARATOR;
        }

        $this->project_root_path = $project_root_path;
    }

    /**
     * Allows to set multiple configurations, instead of calling multiple setters, by passing an associative array with the following structure `configuration_key => configuration_value`
     *
     * @param array<string, mixed> $configurations
     * 
     * @throws InvalidConfigurationException
     */
    public function configure(array $configurations): self {
        foreach($configurations as $configuration_name => $configuration_value){
            $method = "set".implode(array_map("ucfirst", explode("_", $configuration_name)));
            
            if(! method_exists($this, $method)) {
                throw new InvalidConfigurationException($configuration_name);
            }

            $this->{$method}($configuration_value);
        }

        return $this;
    }

    /**
     * Allows to replace a generated singletone with the specified object. This should only be used in testing when reflecting a singletone.
     *
     * @param class-string $class
     * @param object $object must be an instance of `@see $class`
     *
     * @throws RuntimeException
     */
    public function replaceSingletone(string $class, object $object): self {
        if(! $object instanceof $class) {
            throw new RuntimeException("passed object is not an instance of {$class}");
        }

        $this->singletones[$class] = $object;
        return $this;
    }
    
}