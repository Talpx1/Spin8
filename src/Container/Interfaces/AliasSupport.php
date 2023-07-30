<?php declare(strict_types=1);

namespace Spin8\Container\Interfaces;

interface AliasSupport {

    public function hasAlias(string $id): bool;

    /**
     * @param class-string $class
     */
    public function alias(string $alias, string $class): void;
}