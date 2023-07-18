<?php declare(strict_types=1);

namespace Spin8\Configs\Exceptions;
use Exception;
use Throwable;

class ConfigFileMissingException extends Exception {
    public function __construct(string $file_name, int $code = 0, ?Throwable $previous = null) {
        $message = "Unable to retrive {$file_name}.php in ".configPath().". The file is missing.";
        parent::__construct($message, $code, $previous);
    }
}
