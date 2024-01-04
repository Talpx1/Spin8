<?php

namespace Spin8\Tests\Unit\Console\Commands;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Console\Command;
use Spin8\Console\Commands\Generate;
use Spin8\Facades\Config;
use Spin8\Tests\TestCase;

#[CoversClass(Generate::class)]
#[CoversClass(Command::class)]
final class GenerateTest extends TestCase {   
    
    /**
     * @param string[] $flags
     * @param string[] $args
     */
    #[Test]
    #[DataProvider('help_flags_and_args_provider')]
    public function test_it_shows_help_if_it_receive_help_flag(array $flags, array $args): void {
        $this->expectOutputString($this->getHelpMessageForCommand(Generate::class));

        $generate_command = new Generate($flags, $args);

        $generate_command->maybeExecute();
    }

    #[Test]
    public function test_it_shows_help_on_execute_if_there_are_no_arguments(): void {
        $this->expectOutputString($this->getHelpMessageForCommand(Generate::class));       

        (new Generate(args: []))->execute();
    }

    #[Test]
    public function test_it_shows_help_on_execute_if_receives_invalid_subject(): void {
        $this->expectOutputString(
            "Test is not a valid subject".PHP_EOL.PHP_EOL.
            $this->getHelpMessageForCommand(Generate::class)
        );       

        (new Generate(args: ["test"]))->execute();
    }

    #[Test]
    public function test_it_runs_appropriate_generate_method_if_it_receives_valid_subject(): void {
        $generate_command = $this->partialMockWithConstructorArgs(Generate::class, ['generateHeaders'], ['flags'=>[], "args"=>['headers']]); 

        $generate_command->expects($this->once())->method("generateHeaders");

        // @phpstan-ignore-next-line
        $generate_command->execute();
    }

    #[Test]
    public function test_it_shows_correct_help_message(): void {
        $this->expectOutputString(<<<HELP
        Usage: php spin8 generate [subject] [<flags>]

        Description: run the generation of the specified subject.

        Valid subjects:
        headers   -   generates the plugin file's header comments

        Available flags for this command:
        -h, --help, -help: display this message


        HELP);       

        (new Generate())->showHelp();
    }

    #[Test]
    public function test_it_generates_headers(): void {
        Config::set('plugin', 'name', 'Name Test'); 
        Config::set('plugin', 'namespace', 'NameSpaceTest'); 
        Config::set('plugin', 'author', 'Author Test'); 
        Config::set('plugin', 'license', 'License Test');
        Config::set('plugin', 'uri', 'https://plugin-uri.test');
        Config::set('plugin', 'description', 'Description Test');
        Config::set('plugin', 'version', '0.1');
        Config::set('environment', 'min_wordpress_version', '8.3');
        Config::set('environment', 'min_php_version', '6.4.2');
        Config::set('plugin', 'author_uri', 'https://author-uri.test');
        Config::set('plugin', 'license_uri', 'https://license-uri.test');
        Config::set('plugin', 'slug', 'slug-test');
        Config::set('plugin', 'update_uri', 'https://update-uri.test');

        $plugin_file = vfsStream::newFile('slug-test.php')->at($this->filesystem_root)->setContent(<<<CONTENT
        <?php declare(strict_types=1);

        /**
         * %PLUGIN_NAME%
         *
         * @package           %PLUGIN_NAMESPACE%
         * @author            %PLUGIN_AUTHOR%
         * @copyright         %YEAR% %PLUGIN_AUTHOR%
         * @license           %PLUGIN_LICENSE%
         *
         * @wordpress-plugin
         * Plugin Name:       %PLUGIN_NAME%
         * Plugin URI:        %PLUGIN_URI%
         * Description:       %PLUGIN_DESCRIPTION%
         * Version:           %PLUGIN_VERSION%
         * Requires at least: %MIN_WORDPRESS_VERSION%
         * Requires PHP:      %MIN_PHP_VERSION%
         * Author:            %PLUGIN_AUTHOR%
         * Author URI:        %PLUGIN_AUTHOR_URI%
         * License:           %PLUGIN_LICENSE%
         * License URI:       %PLUGIN_LICENSE_URI%
         * Text Domain:       %PLUGIN_SLUG%
         * Domain Path:       /languages
         * Update URI:        %PLUGIN_UPDATE_URI%
         */
        CONTENT);
        
        (new Generate(args: ['headers']))->execute();

        $this->assertStringContainsString('Name Test', $plugin_file->getContent()); 
        $this->assertStringContainsString('NameSpaceTest', $plugin_file->getContent()); 
        $this->assertStringContainsString('Author Test', $plugin_file->getContent()); 
        $this->assertStringContainsString('License Test', $plugin_file->getContent());
        $this->assertStringContainsString('https://plugin-uri.test', $plugin_file->getContent());
        $this->assertStringContainsString('Description Test', $plugin_file->getContent());
        $this->assertStringContainsString('0.1', $plugin_file->getContent());
        $this->assertStringContainsString('8.3', $plugin_file->getContent());
        $this->assertStringContainsString('6.4.2', $plugin_file->getContent());
        $this->assertStringContainsString('https://author-uri.test', $plugin_file->getContent());
        $this->assertStringContainsString('https://license-uri.test', $plugin_file->getContent());
        $this->assertStringContainsString('slug-test', $plugin_file->getContent());
        $this->assertStringContainsString('https://update-uri.test', $plugin_file->getContent());
    }
}
