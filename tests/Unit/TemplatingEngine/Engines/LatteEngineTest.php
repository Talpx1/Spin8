<?php

namespace Spin8\Tests\Unit\TemplatingEngine\Engines;

use Latte\Engine;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\TemplatingEngine\Engines\LatteEngine;
use Spin8\TemplatingEngine\TemplatingEngine;
use Spin8\Tests\TestCase;

#[CoversClass(LatteEngine::class)]
#[CoversClass(TemplatingEngine::class)]
final class LatteEngineTest extends TestCase {

    #[Test]
    public function test_it_passes_constructor_params_to_parent(): void {        
        $internal_engine = $this->createMock(Engine::class);
        
        $latte_engine = new LatteEngine($internal_engine);

        $this->assertEquals("latte", $latte_engine->name);
        $this->assertEquals("latte", $latte_engine->extension);
        $this->assertSame($internal_engine, $latte_engine->engine);
    }

    #[Test]
    public function test_it_creates_engine_if_no_engine_is_passed_in_constructor(): void {        
        $latte_engine = new LatteEngine();

        $this->assertEquals("latte", $latte_engine->name);
        $this->assertEquals("latte", $latte_engine->extension);
        $this->assertInstanceOf(Engine::class, $latte_engine->engine);
    }

    #[Test]
    public function test_render_method_calls_render_method_on_internal_engine_with_given_params(): void {        
        $internal_engine = $this->createMock(Engine::class);
        $internal_engine->expects($this->once())->method('render')->with('test', ['test_data' => 123]);
        
        $latte_engine = new LatteEngine($internal_engine);

        $latte_engine->render('test', ['test_data' => 123]);
    }

    #[Test]
    public function test_setTempPath_method_calls_setTempDirectory_method_on_internal_engine_with_given_param(): void {        
        $internal_engine = $this->createMock(Engine::class);
        $internal_engine->expects($this->once())->method('setTempDirectory')->with('test');
        
        $latte_engine = new LatteEngine($internal_engine);

        $latte_engine->setTempPath('test');
    }

}
