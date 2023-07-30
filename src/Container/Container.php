<?php declare(strict_types=1);

namespace Spin8\Container;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Spin8\Container\Configuration\AbstractContainerConfigurator;
use Spin8\Container\Exceptions\AliasException;
use Spin8\Container\Exceptions\AutowiringFailureException;
use Spin8\Container\Exceptions\BindingException;
use Spin8\Container\Exceptions\SingletonException;
use Spin8\Container\Interfaces\AliasSupport;
use Spin8\Container\Interfaces\SingletonSupport;
use Spin8\Guards\GuardAgainstEmptyParameter;
use Spin8\Guards\GuardAgainstNonExistingClassString;

class Container implements ContainerInterface, AliasSupport, SingletonSupport {

    /** @var array<class-string, callable|class-string> */
    protected array $entries = [];

    /** @var array<class-string, object> */
    protected array $singletons = [];

    /** @var array<string, class-string> */
    protected array $aliases = [];
    
    protected bool $is_loading_configurations = false;

    protected AbstractContainerConfigurator $configurator;




    public function get(string $id): mixed {
        GuardAgainstEmptyParameter::check($id);
        
        if($this->hasSingleton($id)) {
            return $this->getSingleton($id);
        }

        if($this->hasEntry($id)) {
            $entry = $this->getEntry($id);
            
            if(is_callable($entry)) {
                return $entry($this);
            }
            
            $id = $entry;
        }
        
        $id = $this->maybeResolveAlias($id);

        /** @var class-string $id */
        return $this->autowire($id);
    }


    /** @return class-string|callable */
    protected function getEntry(string $id): string|callable {
        $id = $this->maybeResolveAlias($id);

        return $this->entries[$id];
    }


    protected function getSingleton(string $id): object {
        $id = $this->maybeResolveAlias($id);

        return $this->singletons[$id];
    }


    public function has(string $id): bool {
        GuardAgainstEmptyParameter::check($id);

        return $this->hasEntry($id) || $this->hasSingleton($id);
    }
    

    public function hasAlias(string $id): bool {
        GuardAgainstEmptyParameter::check($id);

        return isset($this->aliases[$id]);
    }


    public function hasSingleton(string $id): bool {
        GuardAgainstEmptyParameter::check($id);

        $id = $this->maybeResolveAlias($id);

        return isset($this->singletons[$id]);
    }


    public function hasEntry(string $id): bool {
        GuardAgainstEmptyParameter::check($id);

        $id = $this->maybeResolveAlias($id);

        return isset($this->entries[$id]);
    }


    protected function maybeResolveAlias(string $maybe_alias): string {
        return $this->hasAlias($maybe_alias) ? $this->aliases[$maybe_alias] : $maybe_alias;
    }

    /**
     * @param class-string $id
     * @param class-string|callable|null $value
     */
    public function bind(string $id, callable|string|null $value = null): mixed {
        GuardAgainstEmptyParameter::check($id);
        GuardAgainstEmptyParameter::check($value, allow_null: true);

        GuardAgainstNonExistingClassString::check($id, BindingException::class);
        
        if(is_string($value)) {
            GuardAgainstNonExistingClassString::check($value, BindingException::class);
        }

        if(is_null($value)) {
            $value = $id;
        }

        $this->entries[$id] = $value;

        return $this->get($id);
    }


    /**
     * @param class-string $id
     * @param class-string|object|null $value
     */
    public function singleton(string $id, string|object $value = null): mixed {
        GuardAgainstEmptyParameter::check($id);
        GuardAgainstEmptyParameter::check($value, allow_null: true);

        GuardAgainstNonExistingClassString::check($id, SingletonException::class);

        if(is_null($value)) {
            $value = $id;
        }

        if(is_string($value)) {
            GuardAgainstNonExistingClassString::check($value, SingletonException::class);

            $this->singletons[$id] = $this->autowire($value);
            return $this->get($id);
        }

        $this->singletons[$id] = $value;

        return $this->get($id);
    }


    /**
     * @param class-string $class
     */
    public function alias(string $alias, string $class): void {
        GuardAgainstEmptyParameter::check($alias);
        GuardAgainstEmptyParameter::check($class);

        GuardAgainstNonExistingClassString::check($class, AliasException::class);

        $this->aliases[$alias] = $class;
    }

    public function clear(): void {
        $this->entries = [];
        $this->aliases = [];
        $this->singletons = [];
    }


    /** @param class-string $id */
    protected function autowire(string $id): mixed {
        GuardAgainstNonExistingClassString::check($id, AutowiringFailureException::class);

        $reflection_class = new ReflectionClass($id);

        $constructor = $reflection_class->getConstructor();

        if(is_null($constructor)) {
            return new $id;
        }

        $constructor_params = $constructor->getParameters();
        
        if(empty($constructor_params)) {
            return new $id;
        }

        $constructor_doc_comment = $constructor->getDocComment();

        $dependencies = $this->resolveDependencies($constructor_params, $constructor_doc_comment, $id);

        return $reflection_class->newInstanceArgs($dependencies);
    }


    /**
     * @param ReflectionParameter[] $parameters
     * @param class-string $id
     * @return object[]
     */
    protected function resolveDependencies(array $parameters, string|bool $doc_comment, string $id): array {
        return array_map(function(ReflectionParameter $param) use ($id, $doc_comment) {
            $name = $param->getName();
            $type = $param->getType();

            if($this->is_loading_configurations && $type instanceof ReflectionNamedType) {
                $resolve_from_configs = $this->configurator->resolveDependencyFromConfigs($type->getName());

                if($resolve_from_configs !== false) {
                    return $resolve_from_configs;
                }
            }

            if(is_null($type)) {
                if(!$doc_comment === false) {
                    //TODO: TRY GET TYPE FROM ANNOTATIONS
                }

                if($param->isDefaultValueAvailable()) {
                    return $param->getDefaultValue();
                }

                throw new AutowiringFailureException($id, "{$id} uses a non type-hinted parameters for {$name}. Container does not support annotations or attributes yet.");
            }

            if($type instanceof ReflectionUnionType) {
                //TODO: LOOP EACH TYPE
                throw new AutowiringFailureException($id, "{$id} uses a union type parameters for {$name}. Container does not support union types yet.");
            }

            if($type instanceof ReflectionIntersectionType) {
                throw new AutowiringFailureException($id, "{$id} uses an intersection type parameter for {$name}. Container does not support intersection types yet.");
            }

            if($type instanceof ReflectionNamedType) {
                if($type->isBuiltin()) {
                    if($param->isDefaultValueAvailable()) {
                        return $param->getDefaultValue();
                    }

                    throw new AutowiringFailureException($id, "{$id} uses a built-in parameter with no default value for {$name}. Container does not support built-in types with no default value yet.");
                }
                
                return $this->get($type->getName());
            }

            throw new AutowiringFailureException($id, "Unable to resolve dependencies for {$id}. Parameter {$name} uses an unknown type.");

        }, $parameters);
    }

    public function useConfigurator(AbstractContainerConfigurator $configurator): void {
        $this->is_loading_configurations = true;

        $this->configurator = $configurator;
        $configurator->configure($this);

        $this->is_loading_configurations = false;
    }

}