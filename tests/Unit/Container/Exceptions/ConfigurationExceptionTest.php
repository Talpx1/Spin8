<?php

namespace Spin8\Tests\Unit\Container\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Container\Exceptions\ConfigurationException;
use Spin8\Tests\TestCase;

#[CoversClass(ConfigurationException::class)]
final class ConfigurationExceptionTest extends TestCase {

    #[Test]
    public function test_it_appends_message(): void {        
        $exception = new ConfigurationException("Test");

        $this->assertEquals("Invalid container configuration. Test", $exception->getMessage());
    }

    #[Test]
    public function test_it_passes_arguments_to_parent(): void {   
        $prev = new \LogicException();
                
        $exception = new ConfigurationException("Test", 1, $prev);

        $this->assertEquals("Invalid container configuration. Test", $exception->getMessage());
        $this->assertEquals(1, $exception->getCode());
        $this->assertSame($prev, $exception->getPrevious());
    }

}
