<?php declare(strict_types=1);

namespace Spin8\TemplatingEngine;

abstract class TemplatingEngine {

    public function __construct(
        public readonly string $name,
        public readonly string $extension,
        public readonly object $engine,
    ) {}

    /**
     * @param string $path path of the asset ti render
     * @param array<string, mixed> $data data to pass to the asset
     */
    public abstract function render(string $path, array $data = []): void;

    public abstract function setTempPath(string $path): void;

}