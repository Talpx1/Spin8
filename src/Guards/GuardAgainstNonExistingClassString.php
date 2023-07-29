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
     * @param class-string|null $throwable_class the class-string of the exception/error to throw in case `@see $value` is an nonexisting class-string. If null, it defaults to `@see RuntimeException`.
     *
     * @throws RuntimeException if `@see $throwable_class` is not a valid class-string.
     */
    public static function check(string $value, ?string $throwable_class = null): void {
        
        if(is_null($throwable_class)) {
            $throwable_class = RuntimeException::class;
        }

        if(!class_exists($throwable_class) || !(new ReflectionClass($throwable_class))->isSubclassOf(Throwable::class)) {
            throw new RuntimeException("\$throwable_class must be a valid instance of ".Throwable::class.". {$throwable_class} passed.");
        }
        
        if(!class_exists($value)) {
            $caller = debug_backtrace()[1];

            $message  = "{$value} does not reference a valid class." . PHP_EOL;
            $message  .= "Thrown in {$caller['function']}";
            $message  .= array_key_exists('file', $caller) ? " in {$caller['file']}" : "";
            $message  .= array_key_exists('line', $caller) ? " on line {$caller['line']}" : "";
            $message  .= ".".PHP_EOL;

            // @phpstan-ignore-next-line
            throw new $throwable_class($message);
        }
    }
}