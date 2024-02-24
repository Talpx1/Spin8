<?php

namespace Spin8\Tests\Unit\Console\Commands;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Spin8\Console\Command;
use Spin8\Console\Commands\Publish;
use Spin8\Console\Exceptions\CommandException;
use Spin8\Tests\TestCase;

#[CoversClass(Publish::class)]
#[CoversClass(Command::class)]
final class PublishTest extends TestCase {   

    /**
     * @param string[] $flags
     * @param string[] $args
     */
    #[Test]
    #[DataProvider('help_flags_and_args_provider')]
    public function test_it_shows_help_if_it_receive_help_flag_or_has_help_arg(array $flags, array $args): void {
        $this->expectOutputString($this->getHelpMessageForCommand(Publish::class));

        $help_command = new Publish($flags, $args);

        $help_command->maybeExecute();
    }

    #[Test]
    public function test_it_shows_help_on_execute_if_there_are_no_arguments(): void {
        $this->expectOutputString($this->getHelpMessageForCommand(Publish::class));       

        (new Publish(args: []))->execute();
    }

    #[Test]
    public function test_it_publish_spin8_publishable(): void {
        vfsStream::newFile('asset_test.php')->at(            
            $this->vendor_path->getChild("spin8/framework/assets") // @phpstan-ignore-line
        )->setContent('test');

        (new Publish(args: ["assets"]))->execute();

        $this->assertFileExists($this->assets_path->getChild('assets/asset_test.php')->url());
    }

    #[Test]
    public function test_it_throws_CommandException_if_only_one_argument_is_passed_but_its_not_a_spin8_publishable(): void {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(
            <<<MSG
            Impossible to publish 'test_non_spin8_publishable' as it's not a Spin8 publishable and only one argument has been provided. 
            In order to publish a vendor publishable, you need to pass both the vendor name and the publishable name.
            
            Use: spin8 publish <vendor/package> <path | resource>
            or 'spin8 publish --help' to display a guide for the command.         
            MSG
        );

        (new Publish(args: ["test_non_spin8_publishable"]))->execute();
    }

    #[Test]
    public function test_it_throws_CommandException_if_vendor_dir_cant_be_found(): void {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(
            <<<MSG
            Impossible to publish 'fake-vendor/fake-pkg fake_publishable' as 'fake-vendor/fake-pkg' is not installed (not found in vendor folder). 
            Please double check the provided vendor name or install 'fake-vendor/fake-pkg'.        

            Use 'spin8 publish --help' to display a guide for the command.
            MSG
        );

        (new Publish(args: ["fake-vendor/fake-pkg", "fake_publishable"]))->execute();
    }

    #[Test]
    public function test_it_throws_CommandException_if_vendor_publishable_manifest_cant_be_found(): void {
        vfsStream::newDirectory('fake-vendor/fake-pkg')->at($this->vendor_path);

        $this->expectException(CommandException::class);

        $manifest = vendorPath("fake-vendor/fake-pkg/publishables.php");
        $this->expectExceptionMessage(
            <<<MSG
            Impossible to read the publishables manifest ({$manifest}) for package 'fake-vendor/fake-pkg'.
            It may be missing or the current user may lack the permissions to read it.
            MSG
        );

        (new Publish(args: ["fake-vendor/fake-pkg", "fake_publishable"]))->execute();
    }

    #[Test]
    public function test_it_throws_CommandException_if_specified_publishable_is_not_in_vendor_publishable_manifest(): void {
        vfsStream::newDirectory('fake-vendor/fake-pkg')->at($this->vendor_path);
        vfsStream::newFile('publishables.php')->at(
            $this->vendor_path->getChild("fake-vendor/fake-pkg") // @phpstan-ignore-line
        )->setContent("<?php return [];");

        $this->expectException(CommandException::class);

        $manifest = vendorPath("fake-vendor/fake-pkg/publishables.php");
        $this->expectExceptionMessage(
            <<<MSG
            Impossible to publish 'fake_publishable' from 'fake-vendor/fake-pkg'.
            'fake_publishable' is not present in 'fake-vendor/fake-pkg' publishable manifest ($manifest).
            MSG
        );

        (new Publish(args: ["fake-vendor/fake-pkg", "fake_publishable"]))->execute();
    }

    #[Test]
    public function test_it_throws_CommandException_if_specified_publishable_is_not_an_array_in_vendor_publishable_manifest(): void {
        vfsStream::newDirectory('fake-vendor/fake-pkg')->at($this->vendor_path);
        vfsStream::newFile('publishables.php')->at(
            $this->vendor_path->getChild("fake-vendor/fake-pkg") // @phpstan-ignore-line
        )->setContent("<?php return ['fake_publishable' => 'invalid_value'];");

        $this->expectException(CommandException::class);

        $this->expectExceptionMessage(
            <<<MSG
            Impossible to publish 'fake_publishable' from 'fake-vendor/fake-pkg'. Malformed manifest.

            If you are the developer of 'fake-vendor/fake-pkg':
            'fake_publishable' must be an array ["fake_publishable" => ["what_to_publish", "where_to_publish"]].
            MSG
        );

        (new Publish(args: ["fake-vendor/fake-pkg", "fake_publishable"]))->execute();
    }

    #[Test]
    public function test_it_throws_CommandException_if_specified_publishable_is_not_an_array_containing_two_elements_in_vendor_publishable_manifest(): void {
        vfsStream::newDirectory('fake-vendor/fake-pkg')->at($this->vendor_path);
        vfsStream::newFile('publishables.php')->at(
            $this->vendor_path->getChild("fake-vendor/fake-pkg") // @phpstan-ignore-line
        )->setContent("<?php return ['fake_publishable' => ['invalid_value']];");

        $this->expectException(CommandException::class);

        $this->expectExceptionMessage(
            <<<MSG
            Impossible to publish 'fake_publishable' from 'fake-vendor/fake-pkg'. Malformed manifest.

            If you are the developer of 'fake-vendor/fake-pkg':
            'fake_publishable' must be an array ["fake_publishable" => ["what_to_publish", "where_to_publish"]].
            MSG
        );

        (new Publish(args: ["fake-vendor/fake-pkg", "fake_publishable"]))->execute();
    }

    #[Test]
    #[TestWith(['null'])]
    #[TestWith(["''"])]
    public function test_it_throws_CommandException_if_specified_publishable_array_contains_empty_first_value_in_vendor_publishable_manifest(string $invalid_val): void {        
        vfsStream::newDirectory('fake-vendor/fake-pkg')->at($this->vendor_path);
        vfsStream::newFile('publishables.php')->at(
            $this->vendor_path->getChild("fake-vendor/fake-pkg") // @phpstan-ignore-line
        )->setContent("<?php return ['fake_publishable' => [{$invalid_val}, 'valid']];");

        $this->expectException(CommandException::class);

        $this->expectExceptionMessage(
            <<<MSG
            Impossible to publish 'fake_publishable' from 'fake-vendor/fake-pkg'. Malformed manifest.

            If you are the developer of 'fake-vendor/fake-pkg':
            'fake_publishable' must be an array ["fake_publishable" => ["what_to_publish", "where_to_publish"]].
            MSG
        );

        (new Publish(args: ["fake-vendor/fake-pkg", "fake_publishable"]))->execute();
    }

    #[Test]
    #[TestWith(['null'])]
    #[TestWith(["''"])]
    public function test_it_throws_CommandException_if_specified_publishable_array_contains_empty_second_value_in_vendor_publishable_manifest(string $invalid_val): void {        
        vfsStream::newDirectory('fake-vendor/fake-pkg')->at($this->vendor_path);
        vfsStream::newFile('publishables.php')->at(
            $this->vendor_path->getChild("fake-vendor/fake-pkg") // @phpstan-ignore-line
        )->setContent("<?php return ['fake_publishable' => ['valid', {$invalid_val}]];");

        $this->expectException(CommandException::class);

        $this->expectExceptionMessage(
            <<<MSG
            Impossible to publish 'fake_publishable' from 'fake-vendor/fake-pkg'. Malformed manifest.

            If you are the developer of 'fake-vendor/fake-pkg':
            'fake_publishable' must be an array ["fake_publishable" => ["what_to_publish", "where_to_publish"]].
            MSG
        );

        (new Publish(args: ["fake-vendor/fake-pkg", "fake_publishable"]))->execute();
    }

    #[Test]
    public function test_it_throws_CommandException_if_specified_publishable_array_contains_non_string_first_value_in_vendor_publishable_manifest(): void {        
        vfsStream::newDirectory('fake-vendor/fake-pkg')->at($this->vendor_path);
        vfsStream::newFile('publishables.php')->at(
            $this->vendor_path->getChild("fake-vendor/fake-pkg") // @phpstan-ignore-line
        )->setContent("<?php return ['fake_publishable' => [1, 'valid']];");

        $this->expectException(CommandException::class);

        $this->expectExceptionMessage(
            <<<MSG
            Impossible to publish 'fake_publishable' from 'fake-vendor/fake-pkg'. Malformed manifest.

            If you are the developer of 'fake-vendor/fake-pkg':
            'fake_publishable' must be an array ["fake_publishable" => ["what_to_publish", "where_to_publish"]].
            MSG
        );

        (new Publish(args: ["fake-vendor/fake-pkg", "fake_publishable"]))->execute();
    }

    #[Test]
    public function test_it_throws_CommandException_if_specified_publishable_array_contains_non_string_second_value_in_vendor_publishable_manifest(): void {        
        vfsStream::newDirectory('fake-vendor/fake-pkg')->at($this->vendor_path);
        vfsStream::newFile('publishables.php')->at(
            $this->vendor_path->getChild("fake-vendor/fake-pkg") // @phpstan-ignore-line
        )->setContent("<?php return ['fake_publishable' => ['valid', 1]];");

        $this->expectException(CommandException::class);

        $this->expectExceptionMessage(
            <<<MSG
            Impossible to publish 'fake_publishable' from 'fake-vendor/fake-pkg'. Malformed manifest.

            If you are the developer of 'fake-vendor/fake-pkg':
            'fake_publishable' must be an array ["fake_publishable" => ["what_to_publish", "where_to_publish"]].
            MSG
        );

        (new Publish(args: ["fake-vendor/fake-pkg", "fake_publishable"]))->execute();
    }

    #[Test]
    public function test_it_throws_CommandException_if_specified_publishable_path_in_vendor_publishable_manifest_does_not_exists(): void {        
        vfsStream::newDirectory('fake-vendor/fake-pkg')->at($this->vendor_path);
        vfsStream::newFile('publishables.php')->at(
            $this->vendor_path->getChild("fake-vendor/fake-pkg") // @phpstan-ignore-line
        )->setContent("<?php return ['fake_publishable' => ['non-existing-path', 'valid']];");

        $this->expectException(CommandException::class);
        
        $manifest = vendorPath("fake-vendor/fake-pkg/publishables.php");
        $this->expectExceptionMessage(
            <<<MSG
            Impossible to publish 'fake_publishable' from 'fake-vendor/fake-pkg'. Publishable path not found or not accessible.

            If you are the developer of 'fake-vendor/fake-pkg':
            It looks like 'non-existing-path', defined in your package publishables manifest ({$manifest}), does not exists or can not be read.
            Please double check the path 'non-existing-path' and it's permissions, or the manifest entry
            ["fake_publishable" => ["non-existing-path", "valid"]].
            MSG
        );

        (new Publish(args: ["fake-vendor/fake-pkg", "fake_publishable"]))->execute();
    }

    #[Test]
    public function test_it_throws_CommandException_if_specified_publishable_path_in_vendor_publishable_manifest_is_not_readable(): void {        
        vfsStream::newDirectory('fake-vendor/fake-pkg')->at($this->vendor_path);
        
        $vendor_package_path = $this->vendor_path->getChild("fake-vendor/fake-pkg");
        // @phpstan-ignore-next-line
        $publishable_path = vfsStream::newFile('test.txt', 000)->at($vendor_package_path)->url();
        // @phpstan-ignore-next-line
        vfsStream::newFile('publishables.php')->at($vendor_package_path)->setContent("<?php return ['fake_publishable' => ['{$publishable_path}', 'valid']];");
        
        $this->expectException(CommandException::class);
        
        $manifest = vendorPath("fake-vendor/fake-pkg/publishables.php");
        $this->expectExceptionMessage(
            <<<MSG
            Impossible to publish 'fake_publishable' from 'fake-vendor/fake-pkg'. Publishable path not found or not accessible.

            If you are the developer of 'fake-vendor/fake-pkg':
            It looks like '{$publishable_path}', defined in your package publishables manifest ({$manifest}), does not exists or can not be read.
            Please double check the path '{$publishable_path}' and it's permissions, or the manifest entry
            ["fake_publishable" => ["{$publishable_path}", "valid"]].
            MSG
        );

        (new Publish(args: ["fake-vendor/fake-pkg", "fake_publishable"]))->execute();
    }

    #[Test]
    public function test_it_publish_vendor_publishable(): void {        
        vfsStream::newDirectory('fake-vendor/fake-pkg')->at($this->vendor_path);
        
        $vendor_package_path = $this->vendor_path->getChild("fake-vendor/fake-pkg");
        
        // @phpstan-ignore-next-line
        $publishable_path = vfsStream::newFile('test.txt')->at($vendor_package_path)->url();

        $destination_path = rootPath('test2.md');

        // @phpstan-ignore-next-line
        vfsStream::newFile('publishables.php')->at($vendor_package_path)->setContent("<?php return ['fake_publishable' => ['{$publishable_path}', '{$destination_path}']];");
        
        $this->assertFileDoesNotExist($destination_path);
        
        (new Publish(args: ["fake-vendor/fake-pkg", "fake_publishable"]))->execute();

        $this->assertFileExists($destination_path);
    }

    #[Test]
    public function test_it_shows_correct_help_message(): void {
        $this->expectOutputString(<<<HELP
        Usage:
        - publish a Spin8 publishable: spin8 publish [publishables]
        - publish a vendor publishable: spin8 publish [vendor] [publishable]

        Description: publish a publishable (assets, configs, ...) from an installed package.

        Valid Spin8 publishables:
        assets   -   publish the Spin8 default assets (input template, select template, ...) 

        Available flags for this command:
        -h, --help, -help: display this message


        HELP);       

        (new Publish())->showHelp();
    }
}
