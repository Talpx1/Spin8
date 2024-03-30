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
    public function test_it_build_message_with_the_given_config_key(): void {
        $exception = new ConfigKeyMissingException('test_file.test_key');

        $this->assertSame("Unable to retrieve config: there's no loaded config named test_file.test_key.", $exception->getMessage());
    }

    #[Test]
    public function test_it_accepts_code(): void {
        $exception = new ConfigKeyMissingException('test_file.test_key', 500);

        $this->assertSame(500, $exception->getCode());
    }

    #[Test]
    public function test_it_accepts_previous_exception(): void {
        $prev_exception = new Exception('prev');
        $exception = new ConfigKeyMissingException('test_file.test_key', previous: $prev_exception);

        $this->assertSame($prev_exception, $exception->getPrevious());
        $this->assertSame('prev', $exception->getPrevious()->getMessage());
    }
    
}
