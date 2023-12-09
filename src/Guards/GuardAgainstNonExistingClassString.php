<?php declare(strict_types=1);

namespace Spin8\Guards;

use ReflectionClass;
use RuntimeException;
use Throwable;

final class GuardAgainstNonExistingClassString{

    /**
     * Assure that the passed class-string references an existing class
     *
     * @param string $value the class-string to check.
     * @param callable(string=):\Throwable|class-string $throwable the exception/error to throw in case `@see $value` is an nonexisting class-string.  
     * If null, it defaults to `@see RuntimeException`.  
     * Pass a callable that returns an instance of `@see Throwable` if you need to pass custom parameters to your exception, that callable always accept one parameter which is the message of the thrown exception.  
     * Pass a class-string of a class that implements `@see Throwable` otherwise.
     * @param bool $consider_interfaces whether to consider interfaces as valid classes. Defaults to false.
     *
     * @throws RuntimeException if `@see $throwable` is not a valid class-string.
     */
    public static function check(string $value, callable|string $throwable = RuntimeException::class, bool $consider_interfaces = false): void {

        if(is_string($throwable) && (!class_exists($throwable) || !is_subclass_of($throwable, Throwable::class))) {
            throw new RuntimeException("\$throwable must be a valid instance of ".Throwable::class.". {$throwable} passed.");
        }

        $should_throw = $consider_interfaces ? !class_exists($value) && !interface_exists($value) : !class_exists($value);

        if($should_throw) {
            $caller = debug_backtrace()[1];

            $message  = "'{$value}' does not reference a valid class." . PHP_EOL;
            $message  .= "Thrown in function '{$caller['function']}'";
            $message  .= array_key_exists('file', $caller) ? " called in {$caller['file']}" : "";
            $message  .= array_key_exists('line', $caller) ? " on line {$caller['line']}" : "";
            $message  .= ".".PHP_EOL;

            is_string($throwable) ? throw new $throwable($message) : throw $throwable($message);
        }
    }
}