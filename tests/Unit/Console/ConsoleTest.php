<?php

namespace Spin8\Tests\Unit\Console;


use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Console\Command;
use Spin8\Console\Commands\Help;
use Spin8\Console\Console;
use Spin8\Console\Exceptions\InvalidCommandException;
use Spin8\Console\Exceptions\MissingExecuteMethodException;
use Spin8\Facades\Config;
use Spin8\Tests\TestCase;

#[CoversClass(Console::class)]
final class ConsoleTest extends TestCase {

    #[Test]
    public function test_it_handles_command() : void {
        $command = new class([], []) extends Command{
            public function showHelp(): void {echo 'test help';}
            public function execute(): void {echo 'test execute';}
        };

        \Safe\class_alias($command::class, "\\Spin8\\Console\\Commands\\Test");
        
        $this->expectOutputString('test execute');

        (new Console())->handle(['test']);
    }

    #[Test]
    public function test_it_handles_user_defined_command() : void {
        Config::set('plugin', 'namespace', 'TestNamespace');

        $command = new class() extends Command{
            public function showHelp(): void {echo 'test help';}
            public function execute(): void {echo 'test execute';}
        };

        \Safe\class_alias($command::class, "\\".config('plugin', 'namespace')."\\Console\\Commands\\Test");
        
        $this->expectOutputString('test execute');

        (new Console())->handle(['test']);
    }

    #[Test]
    public function test_it_throws_InvalidCommandException_if_command_class_does_not_exists() : void {
        $this->expectException(InvalidCommandException::class);
        $this->expectExceptionMessage("'test2' is not a valid Spin8 command. Use the 'help' command for a list of commands.");

        (new Console())->handle(['test2']);
    }

    #[Test]
    public function test_it_throws_InvalidCommandException_if_command_class_does_not_subclass_Command() : void {
        $command = new class{};
        \Safe\class_alias($command::class, "\\Spin8\\Console\\Commands\\Test2");

        $this->expectException(InvalidCommandException::class);
        $this->expectExceptionMessage("'test2' is not a valid Spin8 command. Use the 'help' command for a list of commands.");

        (new Console())->handle(['test2']);
    }

    #[Test]
    public function test_if_no_command_is_supplied_help_command_is_invoked() : void {
        $this->expectOutputString($this->getHelpMessageForCommand(Help::class));

        (new Console())->handle([]);
    }

    #[Test]    
    public function test_it_throws_MissingExecuteMethodException_if_command_has_no_execute_method(): void {
        $command = new class extends Command{
            public function showHelp(): void {echo 'test help';}
        };

        \Safe\class_alias($command::class, "\\Spin8\\Console\\Commands\\Test3");

        $this->expectException(MissingExecuteMethodException::class);
        $this->expectExceptionMessage("'".$command::class."' does not implement an execute method. In order for a Spin8 commands to be executed, an execute method must be provided. This method should be public and return void. It can accept any parameter as it's going to be autowired by the container.");
        
        (new Console())->handle(['test3']);
    }
}
