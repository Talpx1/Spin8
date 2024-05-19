<?php declare(strict_types=1);

namespace Spin8\Tests\Unit\Support;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Support\Path;
use Spin8\Tests\TestCase;

#[CoversClass(Path::class)]
final class PathTest extends TestCase {

    protected Path $path;

    public function setUp(): void {
        parent::setUp();
        $this->path = container()->get('support.path');
    }

    #[Test]
    public function test_append_method_correctly_append_path_to_other_path(): void { 
        $correct_path = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['test','path','append','path']);
        $this->assertEquals($correct_path, $this->path->append('/test/path/', '/append/path/'));
    }

    #[Test]
    public function test_correct_separators_method_replaces_die_separators_with_current_os_separator(): void { 
        $correct_path = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['test','path','append','path']) . DIRECTORY_SEPARATOR;
        $this->assertEquals($correct_path, $this->path->correctSeparators('\\test\\path\\append\\path\\'));
    }

    #[Test]
    public function test_remove_beginning_separator_removes_all_separators_from_the_path_beginning(): void { 
        $this->assertEquals('a/b/c', $this->path->removeBeginningSeparator('/a/b/c'));
        $this->assertEquals('a\\b\\c', $this->path->removeBeginningSeparator('\\a\\b\\c'));
    }

    #[Test]
    public function test_remove_ending_separator_removes_all_separators_from_the_path_ending(): void { 
        $this->assertEquals('/a/b/c', $this->path->removeEndingSeparator('/a/b/c/'));
        $this->assertEquals('\\a\\b\\c', $this->path->removeEndingSeparator('\\a\\b\\c\\'));
    }

    #[Test]
    public function test_remove_extremity_separator_removes_all_separators_from_the_path_beginning_and_ending(): void { 
        $this->assertEquals('a/b/c', $this->path->removeExtremitySeparators('/a/b/c/'));
        $this->assertEquals('a\\b\\c', $this->path->removeExtremitySeparators('\\a\\b\\c\\'));
    }


}