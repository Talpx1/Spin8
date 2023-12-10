<?php

declare(strict_types=1);

namespace Spin8\Container;

use Exception;
use Spin8\Container\Configuration\AbstractContainerConfigurator;
use Spin8\Container\Exceptions\AutowiringFailureException;
use Spin8\Container\Exceptions\BindingException;
use Spin8\Container\Exceptions\CircularReferenceException;
use Spin8\Container\Interfaces\Spin8ContainerContract;
use Spin8\Container\Traits\AliasSupport;
use Spin8\Container\Traits\AutowireSupport;
use Spin8\Container\Traits\IntersectionTypeSupport;
use Spin8\Container\Traits\SingletonSupport;
use Spin8\Guards\GuardAgainstEmptyParameter;
use Spin8\Guards\GuardAgainstNonExistingClassString;

/**
 * @phpstan-import-type IntersectionEntryIdentifier from Spin8ContainerContract
 * @phpstan-import-type StandardEntryIdentifier from Spin8ContainerContract
 * @phpstan-import-type AliasedEntryIdentifier from Spin8ContainerContract
 * @phpstan-import-type EntryIdentifier from Spin8ContainerContract
 * @phpstan-import-type EntryResolver from Spin8ContainerContract
 * @phpstan-import-type IntersectionTypeResolver from Spin8ContainerContract
 */
class Container extends Spin8ContainerContract {
    use AutowireSupport;
    use IntersectionTypeSupport;
    use SingletonSupport;
    use AliasSupport;

    /** @param EntryIdentifier $id */
    public function get(string|array $id, bool $called_by_self = false) : mixed {
        if (!$called_by_self) {
            $this->dependency_chain = [];
        }

        GuardAgainstEmptyParameter::check($id);

        if (is_array($id)) {
            $id = $this->resolveIntersectionType($id);
        }

        /** @var StandardEntryIdentifier|AliasedEntryIdentifier $id */
        if ($this->hasSingleton($id)) {
            return $this->getSingleton($id);
        }

        if ($this->hasEntry($id)) {
            $entry = $this->getEntry($id);

            if (is_callable($entry)) {
                return $entry($this);
            }

            $id = $entry;
        }

        $id = $this->maybeResolveAlias($id);

        /** @var StandardEntryIdentifier $id */
        if (in_array($id, $this->dependency_chain)) {
            throw new CircularReferenceException($id, $this->dependency_chain);
        }

        $this->dependency_chain[] = $id;

        return $this->autowire($id);
    }

    /**
     * @param StandardEntryIdentifier|AliasedEntryIdentifier $id
     * @return StandardEntryIdentifier|callable
     */
    protected function getEntry(string $id) : string|callable {
        $id = $this->maybeResolveAlias($id);

        return $this->entries[$id];
    }

    /** @param EntryIdentifier $id */
    public function has(string|array $id) : bool {
        GuardAgainstEmptyParameter::check($id);

        if (is_array($id)) {
            return $this->hasIntersectionTypeResolver($id);
        }

        return $this->hasEntry($id) || $this->hasSingleton($id);
    }

    /** @param StandardEntryIdentifier|AliasedEntryIdentifier $id */
    public function hasEntry(string $id) : bool {
        GuardAgainstEmptyParameter::check($id);

        $id = $this->maybeResolveAlias($id);

        return isset($this->entries[$id]);
    }

    /**
     * @param IntersectionEntryIdentifier|StandardEntryIdentifier $id
     * @param EntryResolver|null $value
     */
    public function bind(string|array $id, callable|string|null $value = null) : mixed {
        GuardAgainstEmptyParameter::check($id);
        GuardAgainstEmptyParameter::check($value, allow_null: true);

        if (is_string($id)) {
            GuardAgainstNonExistingClassString::check($id, BindingException::class, consider_interfaces: true);
        }

        if (is_string($value)) {
            GuardAgainstNonExistingClassString::check($value, BindingException::class);
        }

        if (is_array($id)) {
            return $this->bindIntersectionTypeResolver($id, $value);
        }

        if (is_null($value)) {
            $value = $id;
        }

        $this->entries[$id] = $value;

        return $this->get($id, called_by_self: true);
    }

    public function clear() : void {
        $this->entries = [];
        $this->aliases = [];
        $this->singletons = [];
        $this->intersection_type_resolvers = [];
        $this->dependency_chain = [];
    }

    public function useConfigurator(AbstractContainerConfigurator $configurator) : void {
        $this->is_loading_configurations = true;

        $this->configurator = $configurator;
        $configurator->configure($this);

        $this->is_loading_configurations = false;
    }

    /** @param EntryIdentifier $needs_resolver */
    protected function tryGetting(string|array $needs_resolver, string $currently_resolving_id) : mixed {
        try {
            return $this->get($needs_resolver, called_by_self: true);
        } catch(Exception $e) {
            $needs_resolver_string = \Safe\json_encode($needs_resolver);
            throw new AutowiringFailureException($currently_resolving_id, "attempt to resolve '{$needs_resolver_string}'", previous: $e);
        }
    }
}
