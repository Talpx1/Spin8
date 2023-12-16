<?php declare(strict_types=1);

namespace Spin8\Container\Interfaces;

interface CallableSupport {
    
    /** 
     * @param string|array{string|object, string}|callable $callable
     * @param array<string|int, mixed>|array{} $params 
     */
    public function call(callable|string|array $callable, array $params = []): mixed;

}