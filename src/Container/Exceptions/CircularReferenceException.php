<?php declare(strict_types=1);

namespace Spin8\Container\Exceptions;
use Throwable;

/** @phpstan-import-type StandardEntryIdentifier from \Spin8\Container\Container */

class CircularReferenceException extends ContainerException {

    /**
     * @param StandardEntryIdentifier $id
     * @param StandardEntryIdentifier[] $dependency_chain
     */
    public function __construct(string $id, array $dependency_chain, int $code = 0, ?Throwable $previous = null) {
        $message = "Circular reference detected in container while trying to resolve '{$id}'.".PHP_EOL;
        $message .= "Dependency Chain:".PHP_EOL;
        $message .= \Safe\json_encode($dependency_chain);

        parent::__construct($message, $code, $previous);
    }

}