<?php declare(strict_types=1);

namespace Spin8\Configs\Exceptions;
use Exception;
use Throwable;

class ConfigFileNotLoadedException extends Exception {
    public function __construct(string $file_name, int $code = 0, ?Throwable $previous = null) {
        $message = "Unable to access config file {$file_name}. The file has not loaded.";
        parent::__construct($message, $code, $previous);
    }
}
