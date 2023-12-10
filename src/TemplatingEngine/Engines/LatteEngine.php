<?php declare(strict_types=1);

namespace Spin8\TemplatingEngine\Engines;
use Latte\Engine;
use Spin8\TemplatingEngine\TemplatingEngine;

/**
 * @property Engine $engine
 */
class LatteEngine extends TemplatingEngine{

    public function __construct(Engine $engine = null) {
        parent::__construct('latte', 'latte', $engine ?? new Engine());
    }

    public function render($path, $data = []): void {
        $this->engine->render($path, $data);
    }

    public function setTempPath(string $path): void {
        $this->engine->setTempDirectory($path);
    }
}