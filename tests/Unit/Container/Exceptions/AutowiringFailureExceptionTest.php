<?php

namespace Spin8\Tests\Unit\Container\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Container\Exceptions\AutowiringFailureException;
use Spin8\Tests\TestCase;

#[CoversClass(AutowiringFailureException::class)]
final class AutowiringFailureExceptionTest extends TestCase {

    #[Test]
    public function test_it_insert_entry_id_in_message(): void {        
        $exception = new AutowiringFailureException("Test");

        $this->assertEquals("Container was unable to autowire Test. You may want to configure a binding for it.", $exception->getMessage());
    }

    #[Test]
    public function test_it_appends_message(): void {        
        $exception = new AutowiringFailureException("Test", "Message Test");

        $this->assertEquals("Container was unable to autowire Test. You may want to configure a binding for it.".PHP_EOL.PHP_EOL."FAILURE:".PHP_EOL."Message Test", $exception->getMessage());
    }

    #[Test]
    public function test_it_passes_arguments_to_parent(): void {   
        $prev = new \LogicException();
                
        $exception = new AutowiringFailureException("Test", "Message", 1, $prev);

        $this->assertEquals("Container was unable to autowire Test. You may want to configure a binding for it.".PHP_EOL.PHP_EOL."FAILURE:".PHP_EOL."Message", $exception->getMessage());
        $this->assertEquals(1, $exception->getCode());
        $this->assertSame($prev, $exception->getPrevious());
    }

}
