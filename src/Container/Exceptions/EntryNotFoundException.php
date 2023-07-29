<?php declare(strict_types=1);

namespace Spin8\Container\Exceptions;

use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class EntryNotFoundException extends ContainerException implements NotFoundExceptionInterface {

    public function __construct(string $entry_id, int $code = 0, ?Throwable $previous = null) {
        $message = "No binding found in Dependency Injection Container for identifier {$entry_id}. Can't autowire a non class string id.";
        parent::__construct($message, $code, $previous);
    }

}