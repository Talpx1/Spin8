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

    #[Test]
    #[DataProvider('help_flags_provider')]
    public function test_it_shows_help_if_it_receive_help_flag(string $flag): void {
        $this->expectOutputString($this->getHelpMessageForCommand(Publish::class));

        $help_command = new Publish([$flag]);

        $help_command->maybeExecute();
    }

    // #[Test]
    // public function test_it_shows_correct_help_message(): void {
    //     $this->expectOutputString();       

    //     (new Publish())->showHelp();
    // }
}
