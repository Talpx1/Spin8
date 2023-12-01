<?php declare(strict_types=1);

namespace Spin8\Container\Configuration;
use Psr\Container\ContainerInterface;
use Spin8\Container\Exceptions\ConfigurationException;
use Spin8\Guards\GuardAgainstEmptyParameter;

/**
 * @phpstan-type ContainerConfiguration array{
 *  templating_engines: array<string, class-string>,
 *  singletons: array<class-string|int, class-string|object>,
 *  entries: array<class-string|int, class-string>,
 *  aliases: array<string, class-string>
 * }
 */
abstract class AbstractContainerConfigurator {
    
    /** @var ContainerConfiguration $configurations */
    protected array $configurations;

    protected ContainerInterface $container;

    /** @param ContainerConfiguration|string $configurations */
    public function __construct(string|array $configurations) {
        GuardAgainstEmptyParameter::check($configurations);

        if(is_array($configurations)) {
            $this->configurations = $configurations;
            return;
        }

        if(!file_exists($configurations)) {
            throw new ConfigurationException("Could not find configuration file in {$configurations}");
        }

        if(!is_readable($configurations)) {
            throw new ConfigurationException("Could not read configuration from file {$configurations}");
        }

        $this->configurations = require_once $configurations;
    }

    public function configure(ContainerInterface $container): void {
        $this->container = $container;
        $this->run();
    }

    protected abstract function run(): void;

    public abstract function resolveDependencyFromConfigs(string $id): mixed;
}