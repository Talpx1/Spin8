<?php declare(strict_types=1);

namespace Spin8\Container;

use Closure;
use Exception;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use Spin8\Container\Configuration\AbstractContainerConfigurator;
use Spin8\Container\Exceptions\AliasException;
use Spin8\Container\Exceptions\AutowiringFailureException;
use Spin8\Container\Exceptions\BindingException;
use Spin8\Container\Exceptions\EntryNotFoundException;
use Spin8\Container\Exceptions\SingletonException;
use Spin8\Container\Interfaces\AliasSupport;
use Spin8\Container\Interfaces\SingletonSupport;
use Spin8\Guards\GuardAgainstEmptyParameter;
use Spin8\Guards\GuardAgainstNonExistingClassString;


/**
 * @phpstan-type IntersectionEntryIdentifier class-string[]
 * @phpstan-type StandardEntryIdentifier class-string
 * @phpstan-type AliasedEntryIdentifier string
 * @phpstan-type EntryIdentifier IntersectionEntryIdentifier|StandardEntryIdentifier|AliasedEntryIdentifier
 * @phpstan-type EntryResolver StandardEntryIdentifier|callable
 * @phpstan-type IntersectionTypeResolver array{intersection_types: class-string[], resolver: EntryResolver}
 */

class Container implements ContainerInterface, AliasSupport, SingletonSupport {

    /** @var array<StandardEntryIdentifier, EntryResolver>*/
    protected array $entries = [];

    /** @var array<StandardEntryIdentifier, object> */
    protected array $singletons = [];

    /** @var IntersectionTypeResolver[] */
    protected array $intersection_type_resolvers = [];

    /** @var array<string, StandardEntryIdentifier> */
    protected array $aliases = [];
    
    protected bool $is_loading_configurations = false;

    protected AbstractContainerConfigurator $configurator;

    protected const UNRESOLVABLE_TYPES = ["string", "float", "bool", "int", "iterable", "mixed", "array", "object", "callable", "resource", Closure::class];




    /** @param EntryIdentifier $id */
    public function get(string|array $id): mixed {
        GuardAgainstEmptyParameter::check($id);

        if(is_array($id)) {
            $id = $this->resolveIntersectionType($id);
        }

        /** @var StandardEntryIdentifier|AliasedEntryIdentifier $id */
        
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

        /** @var StandardEntryIdentifier $id */

        return $this->autowire($id);
    }

    /**
     * @param IntersectionEntryIdentifier $id
     * @return EntryResolver
     */
    protected function resolveIntersectionType(array $id): string|callable {
        foreach($this->intersection_type_resolvers as ["intersection_types" => $intersection_types, "resolver" => $resolver]){
            if($intersection_types !== $id) {
                continue;
            }

            return $resolver;
        }

        throw new EntryNotFoundException($id);
    }

    /** @param IntersectionEntryIdentifier $id */
    public function hasIntersectionTypeResolver(array $id): bool {
        return in_array($id, array_column($this->intersection_type_resolvers, "intersection_types"));
    }


    /**
     * @param StandardEntryIdentifier|AliasedEntryIdentifier $id
     * @return StandardEntryIdentifier|callable
     */
    protected function getEntry(string $id): string|callable {
        $id = $this->maybeResolveAlias($id);

        return $this->entries[$id];
    }


    /** @param StandardEntryIdentifier|AliasedEntryIdentifier $id */
    protected function getSingleton(string $id): object {
        $id = $this->maybeResolveAlias($id);

        return $this->singletons[$id];
    }


    /** @param EntryIdentifier $id */
    public function has(string|array $id): bool {
        GuardAgainstEmptyParameter::check($id);

        if(is_array($id)) {
            return $this->hasIntersectionTypeResolver($id);
        }

        return $this->hasEntry($id) || $this->hasSingleton($id);
    }
    

    public function hasAlias(string $id): bool {
        GuardAgainstEmptyParameter::check($id);

        return isset($this->aliases[$id]);
    }

    /** @param StandardEntryIdentifier|AliasedEntryIdentifier $id */
    public function hasSingleton(string $id): bool {
        GuardAgainstEmptyParameter::check($id);
        
        $id = $this->maybeResolveAlias($id);

        return isset($this->singletons[$id]);
    }


    /** @param StandardEntryIdentifier|AliasedEntryIdentifier $id */
    public function hasEntry(string $id): bool {
        GuardAgainstEmptyParameter::check($id);

        $id = $this->maybeResolveAlias($id);

        return isset($this->entries[$id]);
    }

    /**
     *  @param class-string|string $maybe_alias
     *  @return class-string|string
     */
    protected function maybeResolveAlias(string $maybe_alias): string {
        return $this->hasAlias($maybe_alias) ? $this->aliases[$maybe_alias] : $maybe_alias;
    }

    /**
     * @param IntersectionEntryIdentifier|StandardEntryIdentifier $id
     * @param EntryResolver|null $value
     */
    public function bind(string|array $id, callable|string|null $value = null): mixed {
        GuardAgainstEmptyParameter::check($id);
        GuardAgainstEmptyParameter::check($value, allow_null: true);

        if(is_string($id)) {
            GuardAgainstNonExistingClassString::check($id, BindingException::class, consider_interfaces: true);
        }

        if(is_string($value)) {
            GuardAgainstNonExistingClassString::check($value, BindingException::class);
        }

        if(is_array($id)) {
            return $this->bindIntersectionTypeResolver($id, $value);
        }

        if(is_null($value)) {
            $value = $id;
        }

        $this->entries[$id] = $value;

        return $this->get($id);
    }

    /**
     * @param IntersectionEntryIdentifier $id
     * @param EntryResolver $value
     */
    protected function bindIntersectionTypeResolver(array $id, callable|string|null $value): mixed {
        if(is_null($value)) {
            throw new BindingException("Cant bind an array-entry id (IntersectionType resolver) to itself. You should provide a valid class-string or a callable as a value in order to successfully bind.");
        }

        foreach($id as $class_string){
            GuardAgainstNonExistingClassString::check($class_string, BindingException::class, consider_interfaces: true);
        }

        $this->intersection_type_resolvers[] = ["intersection_types" => $id, "resolver" => $value];

        return $this->get($id);
    }


    /**
     * @param StandardEntryIdentifier $id
     * @param StandardEntryIdentifier|object|null $value
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
     * @param StandardEntryIdentifier $class
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
        $this->intersection_type_resolvers = [];
    }


    /** @param class-string $id */
    protected function autowire(string $id): mixed {
        GuardAgainstNonExistingClassString::check($id, fn(string $message) => new AutowiringFailureException($id, $message));

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
    protected function resolveDependencies(array $parameters, string|false $doc_comment, string $id): array {
        return array_map(function(ReflectionParameter $param) use ($id, $doc_comment) {
            $name = $param->getName();
            $type = $param->getType();

            try {
                $resolved = $this->maybeResolveDependencyFromConfig($type);
                if($resolved !== false) {
                    return $resolved;
                }

                if(is_null($type)) {
                    $resolved = $this->maybeResolveDependencyFromAnnotation($name, $doc_comment, $id);
                    if($resolved !== false) {
                        return $resolved;
                    }
                    
                    throw new AutowiringFailureException($id, "{$id} uses a non type-hinted parameter without an annotation and\or a default value for {$name}. Container is unable to autowire.");
                }

                $resolved = $this->maybeResolveUnionTypeDependency($type, $id, $name);
                if($resolved !== false) {
                    return $resolved;
                }

                $resolved = $this->maybeResolveIntersectionTypeDependency($type, $id);
                if($resolved !== false) {
                    return $resolved;
                }
                if($type instanceof ReflectionIntersectionType) {
                    throw new AutowiringFailureException($id, "{$id} uses an intersection type parameter for {$name}. Container does not support intersection types yet.");
                }

                $resolved = $this->maybeResolveNamedTypeDependency($type, $id, $name);
                if($resolved !== false) {
                    return $resolved;
                }
                
                throw new AutowiringFailureException($id, "Unable to resolve dependencies for {$id}. Parameter {$name} uses an unknown type.");
            } catch(AutowiringFailureException $e) {
                if($param->isDefaultValueAvailable()) {
                    return $param->getDefaultValue();
                }
                
                // if($param->allowsNull()) {
                //     return null;
                // }

                throw new AutowiringFailureException($id, previous: $e);
            }
        }, $parameters);
    }

    public function useConfigurator(AbstractContainerConfigurator $configurator): void {
        $this->is_loading_configurations = true;

        $this->configurator = $configurator;
        $configurator->configure($this);

        $this->is_loading_configurations = false;
    }

    protected function maybeResolveDependencyFromConfig(?ReflectionType $type): mixed {
        if(!$this->is_loading_configurations || !($type instanceof ReflectionNamedType)) {
            return false;
        }
        
        return $this->configurator->resolveDependencyFromConfigs($type->getName());
    }

    protected function maybeResolveDependencyFromAnnotation(string $name, string|false $doc_comment, string $id): mixed {
        if($doc_comment === false) {
            return false;
        }

        $match = [];
        \Safe\preg_match("/(?<=@param).*(?=\\\${$name})/m", $doc_comment, $match);

        if(empty($match)) {
            return false;
        }
        
        $annotated_types = array_map(fn($annotated_type) => str_starts_with($annotated_type, "\\") ? substr($annotated_type, 1) : $annotated_type, explode("|", trim($match[0])));

        foreach($annotated_types as $annotated_type){
            if(in_array($annotated_type, self::UNRESOLVABLE_TYPES)) {
                continue;
            }
            
            return $this->tryGetting($annotated_type, $id);
        }

        return false;
    }

    protected function maybeResolveUnionTypeDependency(ReflectionType $type, string $id, string $name): mixed {
        if(!($type instanceof ReflectionUnionType)) {
            return false;
        }

        foreach($type->getTypes() as $union_type){
            if($union_type instanceof ReflectionNamedType) {
                if(in_array($union_type->getName(), self::UNRESOLVABLE_TYPES)) {
                    continue;
                }

                return $this->tryGetting($union_type->getName(), $id);
            }

            if($union_type instanceof ReflectionIntersectionType) {
                return $this->maybeResolveIntersectionTypeDependency($union_type, $id);
            }
        }

        throw new AutowiringFailureException($id, "{$id} uses a union type parameter for {$name}, but container couldn't resolve any of the types. You may want to bind some of those types manually.");
    }

    protected function maybeResolveNamedTypeDependency(ReflectionType $type, string $id, string $name): mixed {
        if(!($type instanceof ReflectionNamedType)) {
            return false;
        }

        if(!$type->isBuiltin() && !in_array($type->getName(), self::UNRESOLVABLE_TYPES)) {
            return $this->tryGetting($type->getName(), $id);
        }

        throw new AutowiringFailureException($id, "{$id} uses a built-in parameter with no default value for {$name}. Container is unable to autowire.");
    }

    protected function maybeResolveIntersectionTypeDependency(ReflectionType $type, string $id): mixed {
        if(!($type instanceof ReflectionIntersectionType)) {
            return false;
        }

        /** @var class-string[] $named_types */
        $named_types = array_map(function($named_type) {
            if($named_type instanceof ReflectionNamedType) {
                return $named_type->getName();
            }
        }, $type->getTypes());

        return $this->tryGetting($named_types, $id);
    }

    /** @param EntryIdentifier $needs_resolver */
    protected function tryGetting(string|array $needs_resolver, string $currently_resolving_id): mixed {
        try{
            return $this->get($needs_resolver);
        } catch(Exception $e) {
            $needs_resolver_string = \Safe\json_encode($needs_resolver);
            throw new AutowiringFailureException($currently_resolving_id, "attempt to resolve '{$needs_resolver_string}'", previous: $e);
        }
    }

}