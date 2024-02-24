<?php declare(strict_types=1);

namespace Spin8\Console\Commands;

use Spin8\Console\Command;
use Spin8\Facades\Config;

class Generate extends Command {

    public function execute(): void {
        if(empty($this->args)) {
            $this->showHelp();
            return;
        }

        $subject = ucfirst(strtolower($this->args[0]));

        if(method_exists($this, "generate{$subject}")) {
            $this->{"generate{$subject}"}();

            return;
        }

        echo "{$subject} is not a valid subject".PHP_EOL.PHP_EOL;
        $this->showHelp();
    }

    public function showHelp(): void {
        echo <<<HELP
        Usage: spin8 generate [subject] [<flags>]

        Description: run the generation of the specified subject.

        Valid subjects:
        headers   -   generates the plugin file's header comments

        Available flags for this command:
        -h, --help, -help: display this message


        HELP;
    }

    protected function generateHeaders(): void {
        $file = rootPath(config('plugin', 'slug').".php");

        $content = \Safe\file_get_contents($file);

        $replacements = [
            '%PLUGIN_NAME%' => Config::get('plugin', 'name'), 
            '%PLUGIN_NAMESPACE%' => Config::get('plugin', 'namespace'), 
            '%PLUGIN_AUTHOR%' => Config::getOr('plugin', 'author', 'Spin8'), 
            '%YEAR%' => \Safe\date('Y'),
            '%PLUGIN_LICENSE%' => Config::getOr('plugin', 'license', 'MIT'),
            '%PLUGIN_URI%' => Config::getOr('plugin', 'uri', 'https://github.com/Talpx1/Spin8_Project_Template'),
            '%PLUGIN_DESCRIPTION%' => Config::getOr('plugin', 'description', 'An awesome plugin created with Spin8'),
            '%PLUGIN_VERSION%' => Config::get('plugin', 'version'),
            '%MIN_WORDPRESS_VERSION%' => Config::get('environment', 'min_wordpress_version'),
            '%MIN_PHP_VERSION%' => Config::get('environment', 'min_php_version'),
            '%PLUGIN_AUTHOR_URI%' => Config::getOr('plugin', 'author_uri', 'https://github.com/Talpx1/Spin8_Project_Template'),
            '%PLUGIN_LICENSE_URI%' => Config::getOr('plugin', 'license_uri', 'https://opensource.org/license/mit/'),
            '%PLUGIN_SLUG%' => Config::get('plugin', 'slug'),
            '%PLUGIN_UPDATE_URI%' => Config::getOr('plugin', 'update_uri', ''),
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        \Safe\file_put_contents($file, $content);
    }

}