<?php declare(strict_types=1);

namespace Spin8\Console\Exceptions;

use Exception;
use Throwable;

class CommandException extends Exception {//TODO: test

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null) {
        $caller = debug_backtrace()[1];
        
        if(array_key_exists("class", $caller)){
            $class_namespace_array = explode("\\",$caller['class']);
            $command_class_name = strtolower(end($class_namespace_array));

            $message = "Error while executing command '{$command_class_name}'.".PHP_EOL.$message;
        }


        parent::__construct($message, $code, $previous);
    }

}