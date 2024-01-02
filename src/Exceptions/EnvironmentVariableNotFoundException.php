<?php declare(strict_types=1);

namespace Spin8\Exceptions;
use Exception;
use Throwable;

class EnvironmentVariableNotFoundException extends Exception{
    public function __construct(string $env_var_name, int $code = 0, ?Throwable $previous = null) {
        $message = "{$env_var_name} is not an environment variable. Maybe you forgot to set it in .env, or maybe you wanted to access a config.";
        parent::__construct($message, $code, $previous);
    }
}
