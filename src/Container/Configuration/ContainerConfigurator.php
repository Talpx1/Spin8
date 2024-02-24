<?php declare(strict_types=1);

namespace Spin8\Container\Configuration;

use ReflectionClass;
use Spin8\Container\Exceptions\ConfigurationException;
use Spin8\Guards\GuardAgainstNonExistingClassString;
use Spin8\TemplatingEngine\TemplatingEngine;

class ContainerConfigurator extends AbstractContainerConfigurator {

    public function __construct(string|array $configurations) {
        parent::__construct($configurations);
    }

    protected function run(): void {
        $this->configureAliases();
        $this->configureTemplatingEngines();
        $this->configureSingletons();
        $this->configureEntries();
    }


    protected function configureAliases(): void {
        if(!array_key_exists('aliases', $this->configurations) || empty($this->configurations['aliases'])) {
            return;
        }
        
        // @phpstan-ignore-next-line
        if(!is_array($this->configurations['aliases'])) {
            throw new ConfigurationException('The key aliases of a container configuration must be an array. '.gettype($this->configurations['aliases']).' passed.');
        }

        foreach($this->configurations['aliases'] as $alias => $class) {
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
            $this->container->alias($alias, $class);
        }
    }


    protected function configureTemplatingEngines(): void {
        if(!array_key_exists('templating_engines', $this->configurations) || empty($this->configurations['templating_engines'])) {
            return;
        }

        // @phpstan-ignore-next-line
        if(!is_array($this->configurations['templating_engines'])) {
            throw new ConfigurationException('The key templating_engines of a container configuration must be an array. '.gettype($this->configurations['templating_engines']).' passed.');
        }

        foreach($this->configurations['templating_engines'] as $alias => $class) {
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
            if(!is_subclass_of($class, TemplatingEngine::class)) {
                throw new ConfigurationException("A templating engine binding value must be a valid reference to a class that extends ".TemplatingEngine::class.".");
            }

            $this->container->singleton($class);
            $this->container->alias($alias, $class);
        }
    }

    protected function configureSingletons(): void {
        if(!array_key_exists('singletons', $this->configurations) || empty($this->configurations['singletons'])) {
            return;
        }

        // @phpstan-ignore-next-line
        if(!is_array($this->configurations['singletons'])) {
            throw new ConfigurationException('The key singletons of a container configuration must be an array. '.gettype($this->configurations['singletons']).' passed.');
        }
        
        foreach($this->configurations['singletons'] as $id => $value) {
            //! Commented out because afaik array keys can only be strings or ints, making this superfluous.
            //! keeping it just in case i realize thats not the case, removing it soon otherwise.
            // if(!is_string($id) && !is_int($id)) {
            //     throw new ConfigurationException('A singleton binding key must be in format <class string => object> or <no key => class string> in a container configuration.');
            // }

            if(is_string($id)) {
                if(empty($id)) {
                    throw new ConfigurationException("A singleton binding key must be a non-empty string in a container configuration. Empty string passed (empty-like values are considered empty).");
                }

                GuardAgainstNonExistingClassString::check($id, ConfigurationException::class);
                
                if(!is_object($value)) {
                    throw new ConfigurationException("A singleton binding value must be an object when using <class string => binding> in a container configuration. ".gettype($value)." passed.");
                }

                if(! $value instanceof $id) {
                    throw new ConfigurationException("A singleton binding value must be an instance of the class string key when using <class string => binding> in a container configuration. {$id} => ".$value::class." passed.");
                }
                
                $this->container->singleton($id, $value);
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
                
                $this->container->singleton($value);
                continue;
            }

            //! Theoretically, it should never be called... but just in case, might as well throw a little exception.
            // @phpstan-ignore-next-line
            throw new ConfigurationException("Unable to configure a singleton binding. Use <class string => object> or <no key => class string> to configure a singleton.");
            
        }
    }

    protected function configureEntries(): void {
        if(!array_key_exists('entries', $this->configurations) || empty($this->configurations['entries'])) {
            return;
        }
        
        // @phpstan-ignore-next-line
        if(!is_array($this->configurations['entries'])) {
            throw new ConfigurationException('The key entries of a container configuration must be an array. '.gettype($this->configurations['entries']).' passed.');
        }

        foreach($this->configurations['entries'] as $id => $value) {
            //! same as above, {@see line #109}
            // if(!is_string($id) && !is_int($id)) {
            //     throw new ConfigurationException('An entry binding key must be in format <class string => class string> or <no key => class string> in a container configuration.');
            // }

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

                $this->container->bind($id, $value);
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
                
                $this->container->bind($value);
                continue;
            }

            //! same as above, {@see line #149}
            // @phpstan-ignore-next-line
            throw new ConfigurationException("Unable to configure an entry binding. Use <class string => class string> or <no key => class string> to configure an entry.");
            
        }
    }


    /** @param class-string $id */
    public function resolveDependencyFromConfigs(string $id): mixed {
        $singletons_queue = $this->configurations['singletons'] ?? [];

        if(array_key_exists($id, $singletons_queue)) {
            return $this->container->singleton($id, $singletons_queue[$id]);
        }

        /** @var class-string[] $non_obj_singletons */
        $non_obj_singletons = array_filter($singletons_queue, "is_int", ARRAY_FILTER_USE_KEY);
        if(in_array($id, $non_obj_singletons)) {
            return $this->container->singleton($id);
        }

        $entries_queue = $this->configurations['entries'] ?? [];

        if(array_key_exists($id, $entries_queue)) {
            return $this->container->bind($id, $entries_queue[$id]);
        }

        /** @var class-string[] $non_obj_singletons */
        $self_binding_entries = array_filter($entries_queue, "is_int", ARRAY_FILTER_USE_KEY);
        if(in_array($id, $self_binding_entries)) {
            return $this->container->bind($id);
        }

        return false;
    }

}