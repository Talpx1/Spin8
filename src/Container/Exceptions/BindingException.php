<?php declare(strict_types=1);

namespace Spin8\Container\Exceptions;
use Throwable;

class BindingException extends ContainerException {

    public function __construct(string $message, int $code = 0, ?Throwable $previous = null) {
        parent::__construct("Unable to bind. {$message}", $code, $previous);
    }

}