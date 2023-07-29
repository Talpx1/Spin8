<?php

namespace Spin8\Tests\Unit\Configs\Exceptions;


use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Configs\Exceptions\ConfigFileNotLoadedException;
use Spin8\Tests\TestCase;

#[CoversClass(ConfigFileNotLoadedException::class)]
final class ConfigFileNotLoadedExceptionTest extends TestCase {

    #[Test]
    public function test_it_build_message_with_the_given_file_name(): void {
        $exception = new ConfigFileNotLoadedException('test');

        $this->assertSame("Unable to access config file test. The file has not loaded.", $exception->getMessage());
    }

    #[Test]
    public function test_it_accepts_code(): void {
        $exception = new ConfigFileNotLoadedException('test', 500);

        $this->assertSame(500, $exception->getCode());
    }

    #[Test]
    public function test_it_accepts_previous_exception(): void {
        $prev_exception = new Exception('prev');
        $exception = new ConfigFileNotLoadedException('test', previous: $prev_exception);

        $this->assertSame($prev_exception, $exception->getPrevious());
        $this->assertSame('prev', $exception->getPrevious()->getMessage());
    }
    
}
