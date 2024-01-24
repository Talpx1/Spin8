<?php

namespace Spin8\Tests\Unit\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Exceptions\EnvironmentVariableNotFoundException;
use Spin8\Tests\TestCase;

#[CoversClass(EnvironmentVariableNotFoundException::class)]
final class EnvironmentVariableNotFoundExceptionTest extends TestCase {

    #[Test]
    public function test_it_inserts_env_var_name_in_message(): void {        
        $exception = new EnvironmentVariableNotFoundException("Test");

        $this->assertEquals("Test is not an environment variable. Maybe you forgot to set it in .env, or maybe you wanted to access a config.", $exception->getMessage());
    }

    #[Test]
    public function test_it_passes_arguments_to_parent(): void {   
        $prev = new \LogicException();
                
        $exception = new EnvironmentVariableNotFoundException("Test", 1, $prev);

        $this->assertEquals("Test is not an environment variable. Maybe you forgot to set it in .env, or maybe you wanted to access a config.", $exception->getMessage());
        $this->assertEquals(1, $exception->getCode());
        $this->assertSame($prev, $exception->getPrevious());
    }

}
