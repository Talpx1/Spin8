<?php declare(strict_types=1);

namespace Spin8\Exceptions;
use Exception;
use Throwable;

class InvalidConfigurationException extends Exception{
    public function __construct(string $configuration_name, int $code = 0, ?Throwable $previous = null) {
        $message = "{$configuration_name} is not a valid Spin8 configuration.";
        parent::__construct($message, $code, $previous);
    }
}
