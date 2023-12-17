<?php

namespace Spin8\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Spin8\Container\Container;
use Spin8\Exceptions\InvalidConfigurationException;
use Spin8\Spin8;
use Spin8\TemplatingEngine\Engines\LatteEngine;
use Spin8\TemplatingEngine\TemplatingEngine;

#[CoversClass(Spin8::class)]
final class Spin8Test extends \PHPUnit\Framework\TestCase {
    public function tearDown() : void {
        try {
            Spin8::dispose();
        } catch(\Exception $e) {
            //! do nothing, if an exception is thrown it means spin8 wasn't initialized, so does not need to be disposed (theres a check). We just care about disposing it when the test class gets run.
        }

        parent::tearDown();
    }

    #[Test]
    public function test_init_method_throws_RuntimeException_if_called_when_Spin8_is_already_initialized() : void {
        $container = new Container();
        $container->singleton(LatteEngine::class);
        $container->alias('latte', LatteEngine::class);

        $spin8 = Spin8::init($container);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Tried to initialize Spin8 when it was already initialized. Use Spin8::instance in order to get the Spin8 instance.');

        Spin8::init($container);
    }

    #[Test]
    public function test_init_method_add_default_configurations_to_Spin8() : void {
        $container = new Container();
        $container->singleton(LatteEngine::class);
        $container->alias('latte', LatteEngine::class);

        $spin8 = Spin8::init($container);

        $this->assertInstanceOf(LatteEngine::class, $spin8->templating_engine);
        $this->assertStringContainsString('src/../../../../', $spin8->project_root_path);
    }

    #[Test]
    public function test_init_method_add_passed_configurations_to_Spin8() : void {
        $container = new Container();
        $container->singleton(LatteEngine::class);
        $container->alias('latte', LatteEngine::class);

        $templating_engine = new class () extends TemplatingEngine {
            public function __construct() {
                parent::__construct('test', 'test', new \ArrayObject());
            }

            public function render($path, $data = []) : void {
            }

            public function setTempPath(string $path) : void {
            }
        };

        $spin8 = Spin8::init($container, ['project_root_path' => 'test', 'templating_engine' => $templating_engine]);

        $this->assertInstanceOf(\ArrayObject::class, $spin8->templating_engine->engine);
        $this->assertEquals('test/', $spin8->project_root_path);
    }

    #[Test]
    public function test_init_method_returns_Spin8_instance() : void {
        $container = new Container();
        $container->singleton(LatteEngine::class);
        $container->alias('latte', LatteEngine::class);

        $this->assertInstanceOf(Spin8::class, Spin8::init($container));
    }

    #[Test]
    public function test_dispose_method_throw_RuntimeException_if_Spin8_was_not_already_initialized() : void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Tried to dispose Spin8 when it was not yet initialized');

        Spin8::dispose();
    }

    #[Test]
    public function test_dispose_method_reset_Spin8_instance() : void {
        $container = new Container();
        $container->singleton(LatteEngine::class);
        $container->alias('latte', LatteEngine::class);

        Spin8::init($container);

        $this->assertNotNull(Spin8::instance());

        Spin8::dispose();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Tried to get Spin8 instance before initialization. Initialize Spin8 using 'Spin8::init' method.");
        Spin8::instance();
    }

    #[Test]
    public function test_instance_method_throw_RuntimeException_if_Spin8_was_not_already_initialized() : void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Tried to get Spin8 instance before initialization. Initialize Spin8 using 'Spin8::init' method.");

        Spin8::instance();
    }

    #[Test]
    public function test_instance_method_returns_Spin8_instance() : void {
        $container = new Container();
        $container->singleton(LatteEngine::class);
        $container->alias('latte', LatteEngine::class);

        Spin8::init($container);

        $this->assertInstanceOf(Spin8::class, Spin8::instance());
    }

    #[Test]
    public function test_Spin8_is_a_singleton() : void {
        $container = new Container();
        $container->singleton(LatteEngine::class);
        $container->alias('latte', LatteEngine::class);

        $spin8 = Spin8::init($container);

        $this->assertSame($spin8, Spin8::instance());
        $this->assertSame(Spin8::instance(), Spin8::instance());
    }

    #[Test]
    public function test_if_Spin8_is_provided_with_an_invalid_configuration_InvalidConfigurationException_is_thrown() : void {
        $container = new Container();
        $container->singleton(LatteEngine::class);
        $container->alias('latte', LatteEngine::class);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('test is not a valid Spin8 configuration.');

        $spin8 = Spin8::init($container, ['test' => 'test']);
    }
}
