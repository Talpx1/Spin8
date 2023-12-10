<?php declare(strict_types=1);

namespace Spin8\Container\Traits;
use Spin8\Container\Exceptions\AliasException;
use Spin8\Guards\GuardAgainstEmptyParameter;
use Spin8\Guards\GuardAgainstNonExistingClassString;

/**
 * @phpstan-import-type StandardEntryIdentifier from \Spin8\Container\Interfaces\Spin8ContainerContract
 */

trait AliasSupport {
    
    public function hasAlias(string $id): bool {
        GuardAgainstEmptyParameter::check($id);

        return isset($this->aliases[$id]);
    }

    /**
     *  @param class-string|string $maybe_alias
     *  @return class-string|string
     */
    protected function maybeResolveAlias(string $maybe_alias): string {
        return $this->hasAlias($maybe_alias) ? $this->aliases[$maybe_alias] : $maybe_alias;
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

}