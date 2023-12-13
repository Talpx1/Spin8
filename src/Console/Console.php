<?php declare(strict_types=1);

namespace Spin8\Console;
use Spin8\Console\Exceptions\InvalidCommandException;

final class Console { //TODO: test

    /** @var string[] */
    protected array $flags = [];

    /** @var string[] */
    protected array $sub_args = [];

    protected string $command_name = '';
    
    protected const string FALLBACK_COMMAND = 'help';




    /** @param string[] $args */
    public function handle(array $args): void {
        $this->parseCommand($args);
                
        /** @var class-string<Command> $class */
        $class = '\\Spin8\\Console\\Commands\\'.ucfirst($this->command_name);

        if(!class_exists($class) || !is_subclass_of($class, Command::class)) {
            throw new InvalidCommandException($this->command_name);
        }

        (new $class($this->flags, $this->sub_args))->maybeExecute();
    }


    /** @param string[] $args */
    protected function parseCommand(array $args): void {
        foreach($args as $arg) {
            if($this->argIsFlag($arg)) {
                $this->flags[] = $arg;
                continue;
            }

            if(empty($this->command_name)) {
                $this->command_name = $arg;
                continue;
            }

            $this->sub_args[] = $arg;
        }

        if(empty($this->command_name)) {
            $this->command_name = self::FALLBACK_COMMAND;
        }
    }

    protected function argIsFlag(string $arg): bool {
        return substr($arg, 0, 1) === '-';
    }

}