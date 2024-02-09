<?php declare(strict_types=1);

namespace Spin8\TemplatingEngine\Engines;

use Spin8\TemplatingEngine\TemplatingEngine;

/**
 * ! THIS IS A DUMMY ENGINE USED FOR TESTS, DO NOT USE IT AS A TEMPLATING ENGINE !
 */
class BasicEngine extends TemplatingEngine{//TODO: test

    public function __construct() {
        parent::__construct('basic_engine', 'php', new \stdClass());
    }

    public function render($path, $data = []): void {        
        extract($data);
        require $path;
    }

    public function setTempPath(string $path): void {
        //do nothing
    }
}