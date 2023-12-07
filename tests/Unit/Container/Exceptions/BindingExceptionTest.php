<?php

namespace Spin8\Tests\Unit\Container\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Container\Exceptions\BindingException;
use Spin8\Tests\TestCase;

#[CoversClass(BindingException::class)]
final class BindingExceptionTest extends TestCase {

    #[Test]
    public function test_test(): void {        
        //TODO
        $this->assertTrue(true);
    }

}
