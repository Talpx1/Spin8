<?php

namespace Spin8\Tests\Unit;


use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Configs\Exceptions\ConfigFileMissingException;
use Spin8\Tests\TestCase;

#[CoversClass(ConfigFileMissingException::class)]
final class ConfigFileMissingExceptionTest extends TestCase {

    #[Test]
    public function test_it_build_message_with_the_given_file_name(): void {
        $exception = new ConfigFileMissingException('test');

        $this->assertSame("Unable to retrive test.php in ".configPath().". The file is missing.", $exception->getMessage());
    }

    #[Test]
    public function test_it_accepts_code(): void {
        $exception = new ConfigFileMissingException('test', 500);

        $this->assertSame(500, $exception->getCode());
    }

    #[Test]
    public function test_it_accepts_previous_exception(): void {
        $prev_exception = new Exception('prev');
        $exception = new ConfigFileMissingException('test', previous: $prev_exception);

        $this->assertSame($prev_exception, $exception->getPrevious());
        $this->assertSame('prev', $exception->getPrevious()->getMessage());
    }
    
}
