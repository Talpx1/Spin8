<?php

namespace Spin8\Tests\Unit\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Exceptions\InvalidConfigurationException;
use Spin8\Tests\TestCase;

#[CoversClass(InvalidConfigurationException::class)]
final class InvalidConfigurationExceptionTest extends TestCase {

        #[Test]
    public function test_it_inserts_configuration_name_in_message(): void {        
        $exception = new InvalidConfigurationException("Test");

        $this->assertEquals("Test is not a valid Spin8 configuration.", $exception->getMessage());
    }

    #[Test]
    public function test_it_passes_arguments_to_parent(): void {   
        $prev = new \LogicException();
                
        $exception = new InvalidConfigurationException("Test", 1, $prev);

        $this->assertEquals("Test is not a valid Spin8 configuration.", $exception->getMessage());
        $this->assertEquals(1, $exception->getCode());
        $this->assertSame($prev, $exception->getPrevious());
    }

}
