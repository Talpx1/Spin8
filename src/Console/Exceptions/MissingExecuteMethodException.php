<?php declare(strict_types=1);

namespace Spin8\Console\Exceptions;

use Exception;
use Throwable;

class MissingExecuteMethodException extends Exception { //TODO: test

    public function __construct(string $command_class, int $code = 0, Throwable $previous = null) {
        $message = "'{$command_class}' does not implement an execute method. In order for a Spin8 commands to be executed, an execute method must be provided. This method should be public and return void. It can accept any parameter as it's going to be autowired by the container.";

        parent::__construct($message, $code, $previous);
    }

}