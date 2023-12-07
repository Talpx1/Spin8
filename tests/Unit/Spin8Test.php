<?php

namespace Spin8\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Spin8;
use Spin8\Tests\TestCase;

#[CoversClass(Spin8::class)]
final class Spin8Test extends TestCase {

    #[Test]
    public function test_test(): void {        
        //TODO
        $this->assertTrue(true);
    }

}
