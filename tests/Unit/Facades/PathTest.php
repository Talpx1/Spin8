<?php declare(strict_types=1);

namespace Spin8\Tests\Unit\Facades;

use BadMethodCallException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Facades\Facade;
use Spin8\Facades\Path;
use Spin8\Tests\TestCase;

#[CoversClass(Path::class)]
#[CoversClass(Facade::class)]
final class PathTest extends TestCase {
    #[Test]
    public function test_can_append_path_to_other_path(): void { 
        $correct_path = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['test','path','append','path']);
        $this->assertEquals($correct_path, Path::append('/test/path/', '/append/path/'));
    }

    #[Test]
    public function test_can_correct_separators(): void { 
        $correct_path = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['test','path','append','path']) . DIRECTORY_SEPARATOR;
        $this->assertEquals($correct_path, Path::correctSeparators('\\test\\path\\append\\path\\'));
    }

    #[Test]
    public function test_can_remove_beginning_separator(): void { 
        $this->assertEquals('a/b/c', Path::removeBeginningSeparator('/a/b/c'));
        $this->assertEquals('a\\b\\c', Path::removeBeginningSeparator('\\a\\b\\c'));
    }

    #[Test]
    public function test_can_remove_ending_separator(): void { 
        $this->assertEquals('/a/b/c', Path::removeEndingSeparator('/a/b/c/'));
        $this->assertEquals('\\a\\b\\c', Path::removeEndingSeparator('\\a\\b\\c\\'));
    }

    #[Test]
    public function test_can_remove_extremity_separator(): void { 
        $this->assertEquals('a/b/c', Path::removeExtremitySeparators('/a/b/c/'));
        $this->assertEquals('a\\b\\c', Path::removeExtremitySeparators('\\a\\b\\c\\'));
    }

    #[Test]
    public function test_it_can_call_all_allowed_methods(): void {    
        Path::append('a', 'b');
        Path::correctSeparators('a');
        Path::removeBeginningSeparator('a');
        Path::removeEndingSeparator('a');
        Path::removeExtremitySeparators('a');

        $this->expectNotToPerformAssertions();
    }
    
    #[Test]
    public function test_it_throws_BadMethodCallException_calling_non_existing_methods(): void {
        Path::correctSeparators('a');
        
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(Path::class. " does not have a method called nonExistingMethod");

        Path::nonExistingMethod();
    }
}