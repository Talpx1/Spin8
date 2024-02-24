<?php declare(strict_types=1);

namespace Spin8\Tests\Unit\Support;

use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Spin8\Support\Filesystem;
use Spin8\Tests\TestCase;

#[CoversClass(Filesystem::class)]
final class FilesystemTest extends TestCase {

    protected Filesystem $filesystem;

    public function setUp(): void {
        parent::setUp();
        $this->filesystem = container()->get('support.filesystem');
    }

    //FIXME: commented out because symlink function does not work with vfs:// protocol
    // #[Test]
    // public function test_copy_method_create_symlink_if_from_path_is_symlink(): void { 
        
    // }

    #[Test]
    public function test_copy_method_throws_InvalidArgumentException_if_from_param_is_empty_string(): void { 
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("function 'copy' was called with non-allowed empty argument");

        $this->filesystem->copy("", "test");
    }

    #[Test]
    public function test_copy_method_throws_InvalidArgumentException_if_to_param_is_empty_string(): void { 
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("function 'copy' was called with non-allowed empty argument");

        $this->filesystem->copy("test", "");
    }

    #[Test]
    public function test_copy_method_copies_file_if_from_path_is_a_file(): void { 
        $original_file = vfsStream::newFile('original.txt')->at($this->filesystem_root)->setContent('original');

        $this->filesystem->copy($original_file->url(), $this->assets_path->url()."/copied.txt");

        $copied_file = $this->assets_path->getChild("copied.txt")->url();

        $this->assertFileExists($copied_file);
        $this->assertIsFile($copied_file);
        $this->assertStringEqualsFile($copied_file, 'original');
    }

    #[Test]
    public function test_copy_method_creates_destination_dir_if_it_does_not_exists(): void { 
        $original_dir = vfsStream::newDirectory('original')->at($this->filesystem_root);

        $this->filesystem->copy($original_dir->url(), $this->assets_path->url()."/copied");

        $copied_dir = $this->assets_path->getChild("copied")->url();

        $this->assertDirectoryExists($copied_dir);
        $this->assertIsDir($copied_dir);
    }

    #[Test]
    public function test_copy_method_throws_RuntimeException_if_from_directory_cant_be_accessed(): void { 
        vfsStream::newDirectory('non_accessible', 000)->at($this->filesystem_root);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Error while coping 'vfs://root/non_accessible' to 'vfs://root/assets/copied'. Please double check the permissions for 'vfs://root/non_accessible'.");
        
        $this->filesystem->copy($this->filesystem_root->getChild('non_accessible')->url(), $this->assets_path->url()."/copied");
    }

    #[Test]
    public function test_copy_method_recursively_clone_folder_content(): void { 
        $dir_to_clone = vfsStream::newDirectory('to_clone')->at($this->filesystem_root);
        $dir1 = vfsStream::newDirectory('dir1')->at($dir_to_clone);
        vfsStream::newFile('dir1_file1.md')->at($dir1)->setContent("dir1_file1.md");
        vfsStream::newFile('file1.txt')->at($dir_to_clone)->setContent("file1.txt");
        
        $this->filesystem->copy($dir_to_clone->url(), $this->assets_path->url()."/cloned");

        $copied_dir = $this->assets_path->getChild("cloned");
        $this->assertDirectoryExists($copied_dir->url());
        $this->assertIsDir($copied_dir->url());

        /** @var \org\bovigo\vfs\vfsStreamDirectory $copied_dir */
        $dir1_copy = $copied_dir->getChild("dir1");
        $this->assertDirectoryExists($dir1_copy->url());
        $this->assertIsDir($dir1_copy->url());

        /** @var \org\bovigo\vfs\vfsStreamDirectory $dir1_copy */
        $dir1_file1_copy = $dir1_copy->getChild("dir1_file1.md");
        $this->assertFileExists($dir1_file1_copy->url());
        $this->assertIsFile($dir1_file1_copy->url());
        $this->assertStringEqualsFile($dir1_file1_copy->url(), "dir1_file1.md");

         /** @var \org\bovigo\vfs\vfsStreamDirectory $dir1_copy */
        $file1_copy = $copied_dir->getChild("file1.txt");
        $this->assertFileExists($file1_copy->url());
        $this->assertIsFile($file1_copy->url());
        $this->assertStringEqualsFile($file1_copy->url(), "file1.txt");
    }

    #[Test]
    public function test_copy_requireIfExists_throws_InvalidArgumentException_if_path_param_is_empty_string(): void { 
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("function 'requireIfExists' was called with non-allowed empty argument");

        $this->filesystem->requireIfExists("");
    }

    #[Test]
    public function test_requireIfExists_method_require_once_a_file_if_exists_and_use_require_once_is_true(): void { 
        $file = vfsStream::newFile('test.txt')->at($this->filesystem_root)->setContent('test');

        \Safe\ob_start();
        foreach(range(1, 5) as $i){
            $this->filesystem->requireIfExists($file->url(), use_require_once: true);
        }
        $content = ob_get_clean();

        $this->assertEquals('test', $content);        
    }

    #[Test]
    public function test_requireIfExists_method_require_a_file_if_exists(): void { 
        $file = vfsStream::newFile('test.txt')->at($this->filesystem_root)->setContent('test');

        \Safe\ob_start();
        foreach(range(1, 5) as $i){
            $this->filesystem->requireIfExists($file->url(), use_require_once: false);
        }
        $content = ob_get_clean();

        $this->assertEquals('testtesttesttesttest', $content);        
    }

    #[Test]
    public function test_requireIfExists_method_does_not_require_once_a_file_if_it_does_not_exists(): void { 
        \Safe\ob_start();
        foreach(range(1, 5) as $i){
            $this->filesystem->requireIfExists('test', use_require_once: true);
        }
        $content = ob_get_clean();

        $this->assertEmpty($content);        
    }

    #[Test]
    public function test_requireIfExists_method_does_not_require_a_file_if_it_does_not_exists(): void { 
        \Safe\ob_start();
        foreach(range(1, 5) as $i){
            $this->filesystem->requireIfExists('test', use_require_once: true);
        }
        $content = ob_get_clean();

        $this->assertEmpty($content);         
    }

    #[Test]
    public function test_copy_requireOnceIfExists_throws_InvalidArgumentException_if_path_param_is_empty_string(): void { 
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("function 'requireOnceIfExists' was called with non-allowed empty argument");

        $this->filesystem->requireOnceIfExists("");
    }

    #[Test]
    public function test_requireOnceIfExists_method_require_once_a_file_if_exists(): void { 
        $file = vfsStream::newFile('test.txt')->at($this->filesystem_root)->setContent('test');

        \Safe\ob_start();
        foreach(range(1, 5) as $i){
            $this->filesystem->requireOnceIfExists($file->url());
        }
        $content = ob_get_clean();

        $this->assertEquals('test', $content);        
    }

    #[Test]
    public function test_requireOnceIfExists_method_does_not_require_once_a_file_if_it_does_not_exists(): void { 
        \Safe\ob_start();
        foreach(range(1, 5) as $i){
            $this->filesystem->requireOnceIfExists('test');
        }
        $content = ob_get_clean();

        $this->assertEmpty($content);        
    }

}