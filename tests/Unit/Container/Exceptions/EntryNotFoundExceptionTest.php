<?php

namespace Spin8\Tests\Unit\Container\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Container\Exceptions\EntryNotFoundException;
use Spin8\Tests\TestCase;

#[CoversClass(EntryNotFoundException::class)]
final class EntryNotFoundExceptionTest extends TestCase {

    #[Test]
    public function test_it_insert_string_entry_id_in_message(): void {        
        $exception = new EntryNotFoundException("Test");

        $this->assertEquals("No binding found for 'Test' in Dependency Injection Container.", $exception->getMessage());
    }

    #[Test]
    public function test_it_insert_array_entry_id_in_message(): void {        
        // @phpstan-ignore-next-line
        $exception = new EntryNotFoundException(["Test", "Test2"]);

        $this->assertEquals("No binding found for '[\"Test\",\"Test2\"]' in Dependency Injection Container.", $exception->getMessage());
    }

    #[Test]
    public function test_it_passes_arguments_to_parent(): void {   
        $prev = new \LogicException();
                
        $exception = new EntryNotFoundException("Test", 1, $prev);

        $this->assertEquals("No binding found for 'Test' in Dependency Injection Container.", $exception->getMessage());
        $this->assertEquals(1, $exception->getCode());
        $this->assertSame($prev, $exception->getPrevious());
    }

}
