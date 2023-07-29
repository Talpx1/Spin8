<?php declare(strict_types=1);

namespace Spin8\Facades;
use BadMethodCallException;
use RuntimeException;

abstract class Facade {

    /** @var string[] $allowed */
    protected static array $allowed = [];

    protected static function implementor(): string {
        throw new RuntimeException('The implementor method is intended to be overridden in the child facade, to reference the class that implements the methods proxied by the facade.');
    }
    
    /** @param mixed[] $args */
    public static function __callStatic(string $method, array $args): mixed {
        $implementor_instance  = container()->get(static::implementor());

        if(! method_exists($implementor_instance, $method) || !in_array($method, static::$allowed)) {
            throw new BadMethodCallException(static::class . " does not have a method called {$method}");
        }

        // @phpstan-ignore-next-line
        return call_user_func_array([$implementor_instance, $method], $args);
    }

}