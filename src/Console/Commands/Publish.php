<?php declare(strict_types=1);

namespace Spin8\Console\Commands;

use Spin8\Console\Command;
use Spin8\Console\Exceptions\CommandException;
use Spin8\Facades\Filesystem;

class Publish extends Command {

    protected const string VENDOR_PUBLISHABLES_MANIFEST = 'publishables.php';

    /** @return array<string, string> */
    protected function publishables(): array{
        return [
            "assets" => rootPath("assets")
        ];
    }

    public function execute(): void {
        if(empty($this->args)) {
            $this->showHelp();
            return;
        }

        $publishable  = $this->args[0];
        
        if(in_array($publishable, array_keys(self::publishables()))) { //TODO: check if already published and avoid override
            Filesystem::copy(frameworkPath($publishable), self::publishables()[$publishable]); 
            return;
        }

        if(count($this->args) === 1) {
            throw new CommandException(
                <<<MSG
                Impossible to publish '{$publishable}' as it's not a Spin8 publishable and only one argument has been provided. 
                In order to publish a vendor publishable, you need to pass both the vendor name and the publishable name.
                
                Use: spin8 publish <vendor/package> <path | resource>
                or 'spin8 publish --help' to display a guide for the command.         
                MSG
            );
        } 

        $vendor  = $this->args[0];
        $publishable  = $this->args[1];

        if(!is_dir(vendorPath($vendor))){
            throw new CommandException(
                <<<MSG
                Impossible to publish '{$vendor} {$publishable}' as '{$vendor}' is not installed (not found in vendor folder). 
                Please double check the provided vendor name or install '{$vendor}'.        

                Use 'spin8 publish --help' to display a guide for the command.
                MSG
            );
        }


        $manifest = vendorPath("{$vendor}/".self::VENDOR_PUBLISHABLES_MANIFEST);
        
        if(!file_exists($manifest) || !is_readable($manifest)) {
            throw new CommandException(
                <<<MSG
                Impossible to read the publishables manifest ({$manifest}) for package '{$vendor}'.
                It may be missing or the current user may lack the permissions to read it.
                MSG
            );
        }

        $vendor_publishables = require $manifest;        

        if(!in_array($publishable, array_keys($vendor_publishables))) {
            throw new CommandException(
                <<<MSG
                Impossible to publish '{$publishable}' from '{$vendor}'.
                '{$publishable}' is not present in '{$vendor}' publishable manifest ($manifest).
                MSG
            );
        }

        if(
            !is_array($vendor_publishables[$publishable]) || 
            count($vendor_publishables[$publishable])!==2 ||
            empty($vendor_publishables[$publishable][0]) || !is_string($vendor_publishables[$publishable][0]) ||
            empty($vendor_publishables[$publishable][1]) || !is_string($vendor_publishables[$publishable][1])
        ) {
            throw new CommandException(
                <<<MSG
                Impossible to publish '{$publishable}' from '{$vendor}'. Malformed manifest.

                If you are the developer of '{$vendor}':
                '{$publishable}' must be an array ["{$publishable}" => ["what_to_publish", "where_to_publish"]].
                MSG
            );
        }

        $publishable_path = $vendor_publishables[$publishable][0];
        $copy_destination = $vendor_publishables[$publishable][1];

        if(!file_exists($publishable_path) || !is_readable($publishable_path)) {
            throw new CommandException(
                <<<MSG
                Impossible to publish '{$publishable}' from '{$vendor}'. Publishable path not found or not accessible.

                If you are the developer of '{$vendor}':
                It looks like '{$publishable_path}', defined in your package publishables manifest ({$manifest}), does not exists or can not be read.
                Please double check the path '{$publishable_path}' and it's permissions, or the manifest entry
                ["{$publishable}" => ["{$publishable_path}", "{$copy_destination}"]].
                MSG
            );
        }

        Filesystem::copy($publishable_path, $copy_destination);
    }

    public function showHelp(): void {
        echo <<<HELP
        Usage:
        - publish a Spin8 publishable: spin8 publish [publishables]
        - publish a vendor publishable: spin8 publish [vendor] [publishable]

        Description: publish a publishable (assets, configs, ...) from an installed package.

        Valid Spin8 publishables:
        assets   -   publish the Spin8 default assets (input template, select template, ...) 

        Available flags for this command:
        -h, --help, -help: display this message


        HELP;
    }

}