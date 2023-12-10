<?php

declare(strict_types=1);

namespace Spin8\Container\Traits;

use Spin8\Container\Exceptions\BindingException;
use Spin8\Container\Exceptions\EntryNotFoundException;
use Spin8\Guards\GuardAgainstNonExistingClassString;

/**
 * @phpstan-import-type IntersectionEntryIdentifier from \Spin8\Container\Interfaces\Spin8ContainerContract
 * @phpstan-import-type EntryResolver from \Spin8\Container\Interfaces\Spin8ContainerContract
 */
trait IntersectionTypeSupport {
    /**
     * @param IntersectionEntryIdentifier $id
     * @return EntryResolver
     */
    protected function resolveIntersectionType(array $id) : string|callable {
        foreach ($this->intersection_type_resolvers as ['intersection_types' => $intersection_types, 'resolver' => $resolver]) {
            if ($intersection_types !== $id) {
                continue;
            }

            return $resolver;
        }

        throw new EntryNotFoundException($id);
    }

    /** @param IntersectionEntryIdentifier $id */
    public function hasIntersectionTypeResolver(array $id) : bool {
        return in_array($id, array_column($this->intersection_type_resolvers, 'intersection_types'));
    }

    /**
     * @param IntersectionEntryIdentifier $id
     * @param EntryResolver $value
     */
    protected function bindIntersectionTypeResolver(array $id, callable|string|null $value) : mixed {
        if (is_null($value)) {
            throw new BindingException('Cant bind an array-entry id (IntersectionType resolver) to itself. You should provide a valid class-string or a callable as a value in order to successfully bind.');
        }

        foreach ($id as $class_string) {
            GuardAgainstNonExistingClassString::check($class_string, BindingException::class, consider_interfaces: true);
        }

        $this->intersection_type_resolvers[] = ['intersection_types' => $id, 'resolver' => $value];

        return $this->get($id, called_by_self: true);
    }
}
