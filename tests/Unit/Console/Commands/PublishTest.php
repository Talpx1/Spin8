<?php

namespace Spin8\Tests\Unit\Console\Commands;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Console\Command;
use Spin8\Console\Commands\Publish;
use Spin8\Tests\TestCase;

#[CoversClass(Publish::class)]
#[CoversClass(Command::class)]
final class PublishTest extends TestCase {   

    /**
     * @param string[] $flags
     * @param string[] $args
     */
    #[Test]
    #[DataProvider('help_flags_and_args_provider')]
    public function test_it_shows_help_if_it_receive_help_flag_or_has_help_arg(array $flags, array $args): void {
        $this->expectOutputString($this->getHelpMessageForCommand(Publish::class));

        $help_command = new Publish($flags, $args);

        $help_command->maybeExecute();
    }

    // #[Test]
    // public function test_it_shows_correct_help_message(): void {
    //     $this->expectOutputString();       

    //     (new Publish())->showHelp();
    // }
}
