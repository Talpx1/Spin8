<?php declare(strict_types=1);

namespace Spin8\Container\Exceptions;
use Throwable;

class SingletonException extends ContainerException {

    public function __construct(string $message, int $code = 0, ?Throwable $previous = null) {
        parent::__construct("Unable to bind singleton. {$message}", $code, $previous);
    }

}