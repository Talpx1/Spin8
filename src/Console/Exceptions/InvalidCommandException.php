<?php declare(strict_types=1);

namespace Spin8\Console\Exceptions;

use Exception;
use Throwable;

class InvalidCommandException extends Exception { //TODO: test

    public function __construct(string $command_name, int $code = 0, Throwable $previous = null) {
        $message = "'{$command_name}' is not a valid Spin8 command. Type 'php spin8 --help' for a list of commands.";

        parent::__construct($message, $code, $previous);
    }

}