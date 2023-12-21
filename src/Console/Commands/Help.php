<?php declare(strict_types=1);

namespace Spin8\Console\Commands;

use Spin8\Console\Command;

class Help extends Command {

    public function execute(): void {
        $this->showHelp();
    }

    public function showHelp(): void {
        echo <<<HELP
        Usage: php spin8 [command] [<flags>]

        Description: execute a Spin8 command.

        Standard commands (user-defined commands are not listed):
        help: display this message.
        publish: publish a resource.

        Available flags for this command:
        -h, --help: display this message


        HELP;
    }

}