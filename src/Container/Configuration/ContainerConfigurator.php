<?php declare(strict_types=1);

namespace Spin8\Container\Configuration;

use ReflectionClass;
use Spin8\Container\Container;
use Spin8\Container\Exceptions\ConfigurationException;
use Spin8\Guards\GuardAgainstEmptyParameter;
use Spin8\Guards\GuardAgainstNonExistingClassString;
use Spin8\TemplatingEngine\TemplatingEngine;

/**
 * @phpstan-type ContainerConfiguration array{
 *  templating_engines: array<string, class-string>,
 *  singletons: array<class-string|int, class-string|object>,
 *  entries: array<class-string|int, class-string>,
 *  aliases: array<string, class-string>
 * }
 */
class ContainerConfigurator {

    /** @var ContainerConfiguration $configurations */
    protected static array $configurations;
    
    protected static Container $container;

    public static function run(Container $container): void {
        self::$container = $container;

        $configurations = $container->getConfigurations();
        
        if(is_null($configurations)) {
            return;
        }

        self::$configurations = $configurations;

        /** @param ContainerConfiguration $configurations */

        $container->setIsLoadingConfigurations(true);
        
        self::configureAliases();
        self::configureTemplatingEngines();
        self::configureSingletons();
        self::configureEntries();

        $container->setIsLoadingConfigurations(false);
    }


    protected static function configureAliases(): void {
        if(!array_key_exists('aliases', self::$configurations) || empty(self::$configurations['aliases'])) {
            return;
        }
        
        // @phpstan-ignore-next-line
        if(!is_array(self::$configurations['aliases'])) {
            throw new ConfigurationException('The key aliases of a container configuration must be an array. '.gettype(self::$configurations['aliases']).' passed.');
        }

        foreach(self::$configurations['aliases'] as $alias => $class) {
            if(!is_string($alias)) {
                throw new ConfigurationException('An alias binding key must be a string.');
            }

            if(empty($alias)) {
                throw new ConfigurationException('An alias binding key must be a non-empty string. Empty string passed (empty-like values are considered empty).');
            }

            if(!is_string($class)) {
                throw new ConfigurationException('An alias binding value must be a  string (class string).');
            }

            if(empty($class)) {
                throw new ConfigurationException('An alias binding value must be a non-empty class string. Empty string passed (empty-like values are considered empty).');
            }
            
            GuardAgainstNonExistingClassString::check($class, ConfigurationException::class);
            
            /** @var class-string $class */
            self::$container->alias($alias, $class);
        }
    }


    protected static function configureTemplatingEngines(): void {
        if(!array_key_exists('templating_engines', self::$configurations) || empty(self::$configurations['templating_engines'])) {
            return;
        }

        // @phpstan-ignore-next-line
        if(!is_array(self::$configurations['templating_engines'])) {
            throw new ConfigurationException('The key templating_engines of a container configuration must be an array. '.gettype(self::$configurations['templating_engines']).' passed.');
        }

        foreach(self::$configurations['templating_engines'] as $alias => $class) {
            if(!is_string($alias)) {
                throw new ConfigurationException("A templating engine binding key must be a string in a container configuration. ".gettype($alias)." passed.");
            }

            if(empty($alias)) {
                throw new ConfigurationException("A templating engine binding key must be a non-empty string in a container configuration. Empty string passed (empty-like values are considered empty).");
            }

            if(!is_string($class)) {
                throw new ConfigurationException("A templating engine binding value must be a string (class-string) in a container configuration. ".gettype($class)." passed.");
            }

            if(empty($class)) {
                throw new ConfigurationException("A templating engine binding value must be a non-empty string (class-string) in a container configuration. Empty string passed (empty-like values are considered empty).");
            }

            GuardAgainstNonExistingClassString::check($class, ConfigurationException::class);

            /** @var class-string $class */
            if(!(new ReflectionClass($class))->isSubclassOf(TemplatingEngine::class)) {
                throw new ConfigurationException("A templating engine binding value must be a valid reference to a class that extends ".TemplatingEngine::class.".");
            }

            self::$container->singleton($class);
            self::$container->alias($alias, $class);
        }
    }

    protected static function configureSingletons(): void {
        if(!array_key_exists('singletons', self::$configurations) || empty(self::$configurations['singletons'])) {
            return;
        }

        // @phpstan-ignore-next-line
        if(!is_array(self::$configurations['singletons'])) {
            throw new ConfigurationException('The key singletons of a container configuration must be an array. '.gettype(self::$configurations['singletons']).' passed.');
        }
        
        foreach(self::$configurations['singletons'] as $id => $value) {
            // @phpstan-ignore-next-line
            if(!is_string($id) && !is_int($id)) {
                throw new ConfigurationException('A singleton binding key must be in format <class string => object> or <no key => class string> in a container configuration.');
            }

            if(is_string($id)) {
                if(empty($id)) {
                    throw new ConfigurationException("A singleton binding key must be a non-empty string in a container configuration. Empty string passed (empty-like values are considered empty).");
                }

                GuardAgainstNonExistingClassString::check($id, ConfigurationException::class);
                
                if(!is_object($value)) {
                    throw new ConfigurationException("A singleton binding value must be an object when using <class string => binding> in a container configuration. ".gettype($value)." passed.");
                }

                if(! $value instanceof $id) {
                    throw new ConfigurationException("A singleton binding value an instance of the class string key when using <class string => binding> in a container configuration. {$id} => ".$value::class." passed.");
                }
                
                self::$container->singleton($id, $value);
                continue;
            }

            if(is_int($id)) {
                if(!is_string($value)) {
                    throw new ConfigurationException('A singleton binding value must be a string (class string) when using <no key => binding> in a container configuration. '.gettype($value)." passed.");
                }

                if(empty($value)) {
                    throw new ConfigurationException("A singleton binding value must be a non-empty string in a container configuration. Empty string passed (empty-like values are considered empty).");
                }

                GuardAgainstNonExistingClassString::check($value, ConfigurationException::class);
                
                self::$container->singleton($value);
                continue;
            }

            // @phpstan-ignore-next-line
            throw new ConfigurationException("Unable to configure a singleton binding. Use <class string => object> or <no key => class string> to configure a singleton.");
            
        }
    }

    protected static function configureEntries(): void {
        if(!array_key_exists('entries', self::$configurations) || empty(self::$configurations['entries'])) {
            return;
        }
        
        // @phpstan-ignore-next-line
        if(!is_array(self::$configurations['entries'])) {
            throw new ConfigurationException('The key entries of a container configuration must be an array. '.gettype(self::$configurations['entries']).' passed.');
        }

        foreach(self::$configurations['entries'] as $id => $value) {
            // @phpstan-ignore-next-line
            if(!is_string($id) && !is_int($id)) {
                throw new ConfigurationException('An entry binding key must be in format <class string => class string> or <no key => class string> in a container configuration.');
            }

            if(is_string($id)) {
                if(empty($id)) {
                    throw new ConfigurationException("An entry binding key must be a non-empty string in a container configuration. Empty string passed (empty-like values are considered empty).");
                }

                GuardAgainstNonExistingClassString::check($id, ConfigurationException::class);
                
                if(!is_string($value)) {
                    throw new ConfigurationException("An entry binding value must be a class string when using <class string => class string> bindings in a container configuration. ".gettype($value)." passed.");
                }
                
                if(! (new ReflectionClass($value))->isInstantiable()) {
                    throw new ConfigurationException("An entry binding value must be an instantiable class string when using <class string => class string> bindings in a container configuration. Non instantiable class string passed.");
                }

                self::$container->bind($id, $value);
                continue;
            }

            if(is_int($id)) {
                if(!is_string($value)) {
                    throw new ConfigurationException('An entry binding value must be a string (class string) when using <no key => binding> in a container configuration. '.gettype($value)." passed.");
                }

                if(empty($value)) {
                    throw new ConfigurationException("An entry binding value must be a non-empty string in a container configuration. Empty string passed (empty-like values are considered empty).");
                }

                GuardAgainstNonExistingClassString::check($value, ConfigurationException::class);

                if(! (new ReflectionClass($value))->isInstantiable()) {
                    throw new ConfigurationException("An entry binding value must be an instantiable class string when using <no class => class string> bindings in a container configuration. Non instantiable class string passed. If you are trying to bind an interface or some other non instantiable class, use <class string => class string>");
                }
                
                self::$container->bind($value);
                continue;
            }

            // @phpstan-ignore-next-line
            throw new ConfigurationException("Unable to configure an entry binding. Use <class string => class string> or <no key => class string> to configure an entry.");
            
        }
    }

}