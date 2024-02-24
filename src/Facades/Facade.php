<?php declare(strict_types=1);

namespace Spin8\Facades;
use BadMethodCallException;

abstract class Facade {

    /** @var string[] */
    protected static array $allowed = [];
    protected static bool $implementor_uses_mixins = false; //TODO: test

    /**
     * This method must return a class-string reference to the class that implements the methods proxied by the facade.
     * This reference is resolved by the container, so is also possible to pass an alias.
     */
    abstract protected static function implementor(): string;
    
    /** @param mixed[] $args */
    public static function __callStatic(string $method, array $args): mixed {
        $implementor_instance  = container()->get(static::implementor());

        if((!static::$implementor_uses_mixins && !method_exists($implementor_instance, $method)) || !self::isMethodAllowed($method)) {
            throw new BadMethodCallException(static::class . " does not have a method called {$method}");
        }

        // @phpstan-ignore-next-line
        return call_user_func_array([$implementor_instance, $method], $args);
    }

    private static function isMethodAllowed(string $method): bool{ //TODO: test
        return in_array($method, static::$allowed) || in_array('*', static::$allowed);
    }

}