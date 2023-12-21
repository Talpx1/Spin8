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
    
    #[Test]
    #[DataProvider('help_flags_provider')]
    public function test_it_shows_help_if_it_receive_help_flag(string $flag): void {
        $this->expectOutputString($this->getHelpMessageForCommand(Help::class));

        $help_command = new Help([$flag]);

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
        Usage: php spin8 [command] [<flags>]

        Description: execute a Spin8 command.

        Standard commands (user-defined commands are not listed):
        help: display this message.
        publish: publish a resource.

        Available flags for this command:
        -h, --help: display this message


        HELP);       

        (new Help())->showHelp();
    }
}
