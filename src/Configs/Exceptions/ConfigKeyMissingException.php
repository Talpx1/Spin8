<?php declare(strict_types=1);

namespace Spin8\Configs\Exceptions;
use Exception;
use Throwable;

class ConfigKeyMissingException extends Exception {
    public function __construct(string $key, int $code = 0, ?Throwable $previous = null) {
        $message = "Unable to retrieve config: there's no loaded config named {$key}.";
        parent::__construct($message, $code, $previous);
    }
}
