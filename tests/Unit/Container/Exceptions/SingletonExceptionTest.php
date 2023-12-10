<?php

namespace Spin8\Tests\Unit\Container\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Container\Exceptions\SingletonException;
use Spin8\Tests\TestCase;

#[CoversClass(SingletonException::class)]
final class SingletonExceptionTest extends TestCase {

    #[Test]
    public function test_it_appends_message(): void {        
        $exception = new SingletonException("Test");

        $this->assertEquals("Unable to bind singleton. Test", $exception->getMessage());
    }

    #[Test]
    public function test_it_passes_arguments_to_parent(): void {   
        $prev = new \LogicException();
                
        $exception = new SingletonException("Test", 1, $prev);

        $this->assertEquals("Unable to bind singleton. Test", $exception->getMessage());
        $this->assertEquals(1, $exception->getCode());
        $this->assertSame($prev, $exception->getPrevious());
    }

}
