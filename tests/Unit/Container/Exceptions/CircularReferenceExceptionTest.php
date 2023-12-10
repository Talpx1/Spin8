<?php

namespace Spin8\Tests\Unit\Container\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Container\Exceptions\CircularReferenceException;
use Spin8\Tests\TestCase;

#[CoversClass(CircularReferenceException::class)]
final class CircularReferenceExceptionTest extends TestCase {

    #[Test]
    public function test_it_builds_message(): void {        
        // @phpstan-ignore-next-line
        $exception = new CircularReferenceException("Test", ["dependency1", "dependency2"]);
        $this->assertEquals("Circular reference detected in container while trying to resolve 'Test'.".PHP_EOL."Dependency Chain:".PHP_EOL.'["dependency1","dependency2"]', $exception->getMessage());
    }

    #[Test]
    public function test_it_passes_arguments_to_parent(): void {   
        $prev = new \LogicException();
                
        // @phpstan-ignore-next-line
        $exception = new CircularReferenceException("Test", ["dependency1", "dependency2"], 1, $prev);

        $this->assertEquals("Circular reference detected in container while trying to resolve 'Test'.".PHP_EOL."Dependency Chain:".PHP_EOL.'["dependency1","dependency2"]', $exception->getMessage());
        $this->assertEquals(1, $exception->getCode());
        $this->assertSame($prev, $exception->getPrevious());
    }

}
