<?php

namespace Spin8\Tests\Unit\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Exceptions\InvalidConfigurationException;
use Spin8\Tests\TestCase;

#[CoversClass(InvalidConfigurationException::class)]
final class InvalidConfigurationExceptionTest extends TestCase {

    #[Test]
    public function test_test(): void {        
        //TODO
        $this->assertTrue(true);
    }

}
