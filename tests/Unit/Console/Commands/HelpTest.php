<?php

namespace Spin8\Tests\Unit\Console\Commands;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Console\Command;
use Spin8\Console\Commands\Help;
use Spin8\Tests\TestCase;

#[CoversClass(Help::class)]
#[CoversClass(Command::class)]
final class HelpTest extends TestCase {   
    
    /**
     * @param string[] $flags
     * @param string[] $args
     */
    #[Test]
    #[DataProvider('help_flags_and_args_provider')]
    public function test_it_shows_help_if_it_receive_help_flag(array $flags, array $args): void {
        $this->expectOutputString($this->getHelpMessageForCommand(Help::class));

        $help_command = new Help($flags, $args);

        $help_command->maybeExecute();
    }

    #[Test]
    public function test_it_shows_help_on_execute(): void {
        $this->expectOutputString($this->getHelpMessageForCommand(Help::class));       

        (new Help())->execute();
    }

    #[Test]
    public function test_it_shows_correct_help_message(): void {
        $this->expectOutputString(<<<HELP
        Usage: spin8 [command] [<flags>]

        Description: execute a Spin8 command.

        Standard commands (user-defined commands are not listed):
        
        > Commands available also before starting the container (using the Spin8 binary)
        install     -   launch the Spin8 installation wizard. Also available before starting the container.
        up          -   start the Spin8 containers. Also available before starting the container.
        down        -   stop the Spin8 containers. Also available before starting the container.
        help        -   display this message. Also available before starting the container.
        
        > Commands available only after starting the container
        publish     -   publish a resource.

        Available flags for this command:
        -h, --help: display this message


        HELP);       

        (new Help())->showHelp();
    }
}
