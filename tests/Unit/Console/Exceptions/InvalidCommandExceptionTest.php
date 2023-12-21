<?php

namespace Spin8\Tests\Unit\Console\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Console\Exceptions\InvalidCommandException;
use Spin8\Tests\TestCase;

#[CoversClass(InvalidCommandException::class)]
final class InvalidCommandExceptionTest extends TestCase {

    #[Test]
    public function test_it_appends_command_name(): void {        
        $exception = new InvalidCommandException("Test");

        $this->assertEquals("'Test' is not a valid Spin8 command. Type 'php spin8 --help' for a list of commands.", $exception->getMessage());
    }

    #[Test]
    public function test_it_passes_arguments_to_parent(): void {   
        $prev = new \LogicException();
                
        $exception = new InvalidCommandException("Test", 1, $prev);

        $this->assertEquals("'Test' is not a valid Spin8 command. Type 'php spin8 --help' for a list of commands.", $exception->getMessage());
        $this->assertEquals(1, $exception->getCode());
        $this->assertSame($prev, $exception->getPrevious());
    }

}
