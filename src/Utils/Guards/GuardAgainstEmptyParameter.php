<?php declare(strict_types=1);

namespace Spin8\Utils\Guards;
use InvalidArgumentException;

final class GuardAgainstEmptyParameter{

    /**
     * Assure that the passed parameter is not empty
     *
     * @throws InvalidArgumentException
     */
    public static function check(mixed $parameter_to_check): void {
        $caller = debug_backtrace()[1];
        if(empty($parameter_to_check)) {
            $message  = "{$caller['function']} was called with non-allowed empty argument";
            $message  .= array_key_exists('file', $caller) ? " in {$caller['file']}" : "";
            $message  .= array_key_exists('line', $caller) ? " on line {$caller['line']}" : "";
            $message  .= ".".PHP_EOL;
            $message  .= array_key_exists('args', $caller) ? PHP_EOL."Passed arguments:".PHP_EOL.print_r($caller['args'], true) : "";

            throw new InvalidArgumentException($message);
        }
    }
}