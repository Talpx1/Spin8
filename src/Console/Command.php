<?php declare(strict_types=1);

namespace Spin8\Console;
use Spin8\Console\Exceptions\MissingExecuteMethodException;

abstract class Command { //TODO: test

    /** @var string[] */
    protected const array HELP_FLAGS = ["-h", "--help"];



    /** 
     * @param string[] $flags 
     * @param string[] $args 
     */
    public function __construct(protected array $flags, protected array $args) {}

    public function maybeExecute(): void {
        if($this->shouldShowHelp()) {
            $this->showHelp();
            return;
        }

        if(!method_exists($this, 'execute')) {
            throw new MissingExecuteMethodException($this::class);
        }
        
        container()->call([$this, 'execute']);
    }

    protected abstract function showHelp(): void;
    
    protected function shouldShowHelp(): bool {
        return in_array($this->flags, self::HELP_FLAGS);
    }

}