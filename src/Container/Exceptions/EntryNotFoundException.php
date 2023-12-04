<?php declare(strict_types=1);

namespace Spin8\Container\Exceptions;

use Psr\Container\NotFoundExceptionInterface;
use Throwable;

/** @phpstan-import-type EntryIdentifier from \Spin8\Container\Container */

class EntryNotFoundException extends ContainerException implements NotFoundExceptionInterface {

    /** @param EntryIdentifier $entry_id */
    public function __construct(string|array $entry_id, int $code = 0, ?Throwable $previous = null) {
        $entry_id = is_array($entry_id) ? \Safe\json_encode($entry_id) : $entry_id;

        $message = "No binding found for '{$entry_id}' in Dependency Injection Container.";

        parent::__construct($message, $code, $previous);
    }

}