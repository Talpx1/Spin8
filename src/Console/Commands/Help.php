<?php declare(strict_types=1);

namespace Spin8\Console\Commands;

use Spin8\Console\Command;

class Help extends Command {

    public function execute(): void {
        $this->showHelp();
    }

    public function showHelp(): void {
        echo <<<HELP
        Usage: spin8 [command] [<flags>]

        Description: execute a Spin8 command.

        Standard commands (user-defined commands are not listed):
        
        > Commands available also before starting the container (using the Spin8 binary)
        install     -   launch the Spin8 installation wizard. Also available before starting the container.
        up          -   start the Spin8 containers. Also available before starting the container.
        down        -   stop the Spin8 containers. Also available before starting the container.
        help        -   display this message. Also available before starting the container.
        
        > Commands available only after starting the container
        publish     -   publish a resource.

        Available flags for this command:
        -h, --help: display this message


        HELP;
    }

}