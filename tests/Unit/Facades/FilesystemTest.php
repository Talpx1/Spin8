<?php declare(strict_types=1);

namespace Spin8\Tests\Unit\Facades;

use BadMethodCallException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Spin8\Facades\Facade;
use Spin8\Facades\Filesystem;
use Spin8\Tests\TestCase;

#[CoversClass(Filesystem::class)]
#[CoversClass(Facade::class)]
final class FilesystemTest extends TestCase {
    #[Test]
    public function test_can_copy(): void {
        $dir_to_clone = vfsStream::newDirectory('to_clone')->at($this->filesystem_root);
        vfsStream::newFile('file1.txt')->at($dir_to_clone)->setContent("file1.txt");

        $dir_to_clone_into = vfsStream::newDirectory('to_clone_into')->at($this->filesystem_root);
        $this->assertFalse($dir_to_clone_into->hasChild('file1.txt'));

        Filesystem::copy($dir_to_clone->url(), $dir_to_clone_into->url());

        $this->assertTrue($dir_to_clone_into->hasChild('file1.txt'));
    }

    #[Test]
    public function test_can_require_if_exists(): void {
        $file = vfsStream::newFile('test.txt')->at($this->filesystem_root)->setContent('test');

        \Safe\ob_start();
        foreach(range(1, 5) as $i){
            Filesystem::requireIfExists($file->url(), use_require_once: false);
        }
        $content = ob_get_clean();

        $this->assertEquals('testtesttesttesttest', $content);        
    }

    #[Test]
    public function test_can_require_once_if_exists(): void {
        $file = vfsStream::newFile('test.txt')->at($this->filesystem_root)->setContent('test');

        \Safe\ob_start();
        foreach(range(1, 5) as $i){
            Filesystem::requireOnceIfExists($file->url());
        }
        $content = ob_get_clean();

        $this->assertEquals('test', $content);
    }
    
    #[Test]
    public function test_it_can_call_all_allowed_methods(): void {
        try{Filesystem::copy('a', 'b');}catch(RuntimeException){}
        Filesystem::requireIfExists('a');
        Filesystem::requireOnceIfExists('a');

        $this->expectNotToPerformAssertions();
    }
    
    #[Test]
    public function test_it_throws_BadMethodCallException_calling_non_existing_methods(): void {
        Filesystem::requireIfExists('a');
        
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(Filesystem::class. " does not have a method called nonExistingMethod");

        Filesystem::nonExistingMethod();
    }
}