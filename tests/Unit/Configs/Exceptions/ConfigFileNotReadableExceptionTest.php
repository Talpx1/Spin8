<?php

namespace Spin8\Tests\Unit;


use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Configs\Exceptions\ConfigFileNotReadableException;
use Spin8\Tests\TestCase;

#[CoversClass(ConfigFileNotReadableException::class)]
final class ConfigFileNotReadableExceptionTest extends TestCase {

    #[Test]
    public function test_it_build_message_with_the_given_config_key_and_file_name(): void {
        $exception = new ConfigFileNotReadableException('test_file');

        $this->assertSame("Unable to load config file test_file. File not readable.", $exception->getMessage());
    }

    #[Test]
    public function test_it_accepts_code(): void {
        $exception = new ConfigFileNotReadableException('test_file', 500);

        $this->assertSame(500, $exception->getCode());
    }

    #[Test]
    public function test_it_accepts_previous_exception(): void {
        $prev_exception = new Exception('prev');
        $exception = new ConfigFileNotReadableException('test_file', previous: $prev_exception);

        $this->assertSame($prev_exception, $exception->getPrevious());
        $this->assertSame('prev', $exception->getPrevious()->getMessage());
    }
    
}
