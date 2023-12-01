<?php declare(strict_types=1);

namespace Spin8\Container\Exceptions;
use Throwable;

class AutowiringFailureException extends ContainerException {

    public function __construct(string $entry_id, string $message = "", int $code = 0, ?Throwable $previous = null) {
        $final_message = "Container was unable to autowire {$entry_id}. You may want to configure a binding for it.";
        
        if(!empty($message)) {
            $final_message .= PHP_EOL.PHP_EOL."FAILURE:".PHP_EOL.$message;
        }

        parent::__construct($final_message, $code, $previous);
    }

}