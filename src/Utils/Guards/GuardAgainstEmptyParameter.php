<?php declare(strict_types=1);

namespace Spin8\Utils\Guards;
use InvalidArgumentException;

final class GuardAgainstEmptyParameter{

    /**
     * Assure that the passed parameter is not empty
     *
     * @throws InvalidArgumentException
     */
    public static function check(mixed $parameter_to_check): void{
        $caller = debug_backtrace()[1];
        if(empty($parameter_to_check)) throw new InvalidArgumentException($caller['function']." was called with non-allowed empty argument in ".$caller['file']." line ".$caller['line'].".".PHP_EOL.PHP_EOL."Passed arguments:".PHP_EOL.print_r($caller['args'], true));
    }
}