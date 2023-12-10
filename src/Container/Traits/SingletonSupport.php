<?php declare(strict_types=1);

namespace Spin8\Container\Traits;
use Spin8\Container\Exceptions\SingletonException;
use Spin8\Guards\GuardAgainstEmptyParameter;
use Spin8\Guards\GuardAgainstNonExistingClassString;
/**
 * @phpstan-import-type StandardEntryIdentifier from \Spin8\Container\Interfaces\Spin8ContainerContract
 * @phpstan-import-type AliasedEntryIdentifier from \Spin8\Container\Interfaces\Spin8ContainerContract
 */
trait SingletonSupport {

    /** @param StandardEntryIdentifier|AliasedEntryIdentifier $id */
    public function hasSingleton(string $id): bool {
        GuardAgainstEmptyParameter::check($id);
        
        $id = $this->maybeResolveAlias($id);

        return isset($this->singletons[$id]);
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
            return $this->get($id, called_by_self: true);
        }

        $this->singletons[$id] = $value;

        return $this->get($id, called_by_self: true);
    }

    /** @param StandardEntryIdentifier|AliasedEntryIdentifier $id */
    protected function getSingleton(string $id): object {
        $id = $this->maybeResolveAlias($id);

        return $this->singletons[$id];
    }

}