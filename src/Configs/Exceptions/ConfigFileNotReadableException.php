<?php declare(strict_types=1);

namespace Spin8\Configs\Exceptions;
use Exception;
use Throwable;

class ConfigFileNotReadableException extends Exception{
    public function __construct(string $config_file, int $code = 0, ?Throwable $previous = null) {
        $message = "Unable to load config file {$config_file}. File not readable.";
        parent::__construct($message, $code, $previous);
    }
}
