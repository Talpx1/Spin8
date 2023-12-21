<?php

namespace Spin8\Tests\Unit\Container\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Console\Exceptions\MissingExecuteMethodException;
use Spin8\Tests\TestCase;

#[CoversClass(MissingExecuteMethodException::class)]
final class MissingExecuteMethodExceptionTest extends TestCase {

    #[Test]
    public function test_it_appends_command_name(): void {        
        $exception = new MissingExecuteMethodException("Test");

        $this->assertEquals("'Test' does not implement an execute method. In order for a Spin8 commands to be executed, an execute method must be provided. This method should be public and return void. It can accept any parameter as it's going to be autowired by the container.", $exception->getMessage());
    }

    #[Test]
    public function test_it_passes_arguments_to_parent(): void {   
        $prev = new \LogicException();
                
        $exception = new MissingExecuteMethodException("Test", 1, $prev);

        $this->assertEquals("'Test' does not implement an execute method. In order for a Spin8 commands to be executed, an execute method must be provided. This method should be public and return void. It can accept any parameter as it's going to be autowired by the container.", $exception->getMessage());
        $this->assertEquals(1, $exception->getCode());
        $this->assertSame($prev, $exception->getPrevious());
    }

}
