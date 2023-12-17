<?php declare(strict_types=1);

namespace Spin8\Configs\Exceptions;
use Exception;
use Throwable;

class ConfigKeyMissingException extends Exception {
    public function __construct(string $config_key, string $file_name, int $code = 0, ?Throwable $previous = null) {
        $message = "Unable to retrieve {$config_key} in {$file_name} config file. There's no key named {$config_key}.";
        parent::__construct($message, $code, $previous);
    }
}
