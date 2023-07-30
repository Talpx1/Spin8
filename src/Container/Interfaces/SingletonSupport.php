<?php declare(strict_types=1);

namespace Spin8\Container\Interfaces;

interface SingletonSupport {

    public function hasSingleton(string $id): bool;

    /**
     * @param class-string $id
     * @param class-string|object|null $value
     */
    public function singleton(string $id, string|object $value = null): mixed ;
}