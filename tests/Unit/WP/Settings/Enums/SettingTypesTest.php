<?php

namespace Spin8\Tests\Unit\WP\Settings\Enums;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Tests\TestCase;
use Spin8\WP\Settings\Enums\SettingTypes;

#[CoversClass(SettingTypes::class)]
final class SettingTypesTest extends TestCase {

    #[Test]
    public function test_test(): void {        
        //TODO
        $this->assertTrue(true);
    }

}
