<?php

namespace Spin8\Tests\Unit\Configs\Exceptions;


use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Configs\Exceptions\ConfigKeyMissingException;
use Spin8\Tests\TestCase;

#[CoversClass(ConfigKeyMissingException::class)]
final class ConfigKeyMissingExceptionTest extends TestCase {

    #[Test]
    public function test_it_build_message_with_the_given_config_key_and_file_name(): void {
        $exception = new ConfigKeyMissingException('test_key', 'test_file');

        $this->assertSame("Unable to retrive test_key in test_file config file. There's no key named test_key.", $exception->getMessage());
    }

    #[Test]
    public function test_it_accepts_code(): void {
        $exception = new ConfigKeyMissingException('test_key', 'test_file', 500);

        $this->assertSame(500, $exception->getCode());
    }

    #[Test]
    public function test_it_accepts_previous_exception(): void {
        $prev_exception = new Exception('prev');
        $exception = new ConfigKeyMissingException('test_key', 'test_file', previous: $prev_exception);

        $this->assertSame($prev_exception, $exception->getPrevious());
        $this->assertSame('prev', $exception->getPrevious()->getMessage());
    }
    
}
