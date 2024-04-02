<?php

namespace Spin8\Tests\Unit\Console\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Console\Exceptions\CommandException;
use Spin8\Tests\TestCase;

#[CoversClass(CommandException::class)]
final class CommandExceptionTest extends TestCase {

    #[Test]
    public function test_it_appends_detects_command_and_append_command_name_to_message(): void {        
        $exception = new CommandException("Test");

        $this->assertEquals("Error while executing command 'commandexceptiontest'.".PHP_EOL.'Test', $exception->getMessage());
    }

    #[Test]
    public function test_it_passes_arguments_to_parent(): void {   
        $prev = new \LogicException();
                
        $exception = new CommandException("Test", 1, $prev);

        $this->assertEquals("Error while executing command 'commandexceptiontest'.".PHP_EOL.'Test', $exception->getMessage());
        $this->assertEquals(1, $exception->getCode());
        $this->assertSame($prev, $exception->getPrevious());
    }

}
