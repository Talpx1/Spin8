<?php declare(strict_types=1);

namespace Spin8\Console;
use Spin8\Console\Exceptions\MissingExecuteMethodException;

abstract class Command {

    /** @var string[] */
    protected const array HELP_FLAGS = ["--help", '-help'];



    /** 
     * @param string[] $flags 
     * @param string[] $args 
     */
    public function __construct(protected array $flags = [], protected array $args = []) {}

    public function maybeExecute(): void {        
        if(!empty(array_intersect($this->flags, self::HELP_FLAGS)) || in_array('help', $this->args)) {
            $this->showHelp();
            return;
        }

        if(!method_exists($this, 'execute')) {
            throw new MissingExecuteMethodException($this::class);
        }
        
        container()->call([$this, 'execute']);
    }

    public abstract function showHelp(): void;
}