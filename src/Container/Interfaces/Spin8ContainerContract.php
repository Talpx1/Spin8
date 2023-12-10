<?php declare(strict_types=1);

namespace Spin8\Container\Interfaces;
use Psr\Container\ContainerInterface;
use Spin8\Container\Configuration\AbstractContainerConfigurator;

/**
 * @phpstan-type IntersectionEntryIdentifier class-string[]
 * @phpstan-type StandardEntryIdentifier class-string
 * @phpstan-type AliasedEntryIdentifier string
 * @phpstan-type EntryIdentifier IntersectionEntryIdentifier|StandardEntryIdentifier|AliasedEntryIdentifier
 * @phpstan-type EntryResolver StandardEntryIdentifier|callable
 * @phpstan-type IntersectionTypeResolver array{intersection_types: class-string[], resolver: EntryResolver}
 */

abstract class Spin8ContainerContract implements AliasSupport, SingletonSupport, ContainerInterface {

    //! DEFAULT CONTAINER SUPPORT
    protected bool $is_loading_configurations = false;

    protected AbstractContainerConfigurator $configurator;

    /** @var array<StandardEntryIdentifier, EntryResolver>*/
    protected array $entries = [];

    /**
     * @param IntersectionEntryIdentifier|StandardEntryIdentifier $id
     * @param EntryResolver|null $value
     */
    public abstract function bind(string|array $id, callable|string|null $value = null): mixed;

    public abstract function clear(): void;

    public abstract function useConfigurator(AbstractContainerConfigurator $configurator): void;




    //! SINGLETON SUPPORT
    /** @var array<StandardEntryIdentifier, object> */
    protected array $singletons = [];



    
    //! ALIAS SUPPORT
    /** @var array<string, StandardEntryIdentifier> */
    protected array $aliases = [];




    //! AUTOWIRE SUPPORT
    protected const UNRESOLVABLE_TYPES = ["string", "float", "bool", "int", "iterable", "mixed", "array", "object", "callable", "resource", \Closure::class];

    /** @var StandardEntryIdentifier[] */
    protected array $dependency_chain = [];

    /** @param class-string $id */
    protected abstract function autowire(string $id): mixed;

    /**
     * @param \ReflectionParameter[] $parameters
     * @param class-string $id
     * @return object[]
     */
    protected abstract function resolveDependencies(array $parameters, string|false $doc_comment, string $id): array;




    //! INTERSECTION TYPE SUPPORT
    /** @var IntersectionTypeResolver[] */
    protected array $intersection_type_resolvers = [];

    /**
     * @param IntersectionEntryIdentifier $id
     * @return EntryResolver
     */
    protected abstract function resolveIntersectionType(array $id): string|callable;

    /** @param IntersectionEntryIdentifier $id */
    public abstract function hasIntersectionTypeResolver(array $id): bool;

    /**
     * @param IntersectionEntryIdentifier $id
     * @param EntryResolver $value
     */
    protected abstract function bindIntersectionTypeResolver(array $id, callable|string|null $value): mixed;
}