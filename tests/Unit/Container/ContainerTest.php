<?php

namespace Spin8\Tests\Unit\Container;

use BadMethodCallException;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Container\Configuration\ContainerConfigurator;
use Spin8\Container\Container;
use Spin8\Container\Exceptions\AutowiringFailureException;
use Spin8\Container\Exceptions\CircularReferenceException;
use Spin8\Container\Exceptions\EntryNotFoundException;
use Spin8\Container\Interfaces\Spin8ContainerContract;

/** @phpstan-import-type ContainerConfiguration from \Spin8\Container\Configuration\AbstractContainerConfigurator */
#[CoversClass(Container::class)]
#[CoversClass(Spin8ContainerContract::class)]
final class ContainerTest extends \PHPUnit\Framework\TestCase {
    protected Container $container;

    public function setUp() : void {
        parent::setUp();

        $this->container = new Container();
    }

    public function tearDown() : void {
        unset($this->container);

        parent::tearDown();
    }

    #[Test]
    public function test_throws_InvalidArgumentException_if_get_method_is_called_with_empty_string() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->container->get('');
    }

    #[Test]
    public function test_throws_InvalidArgumentException_if_get_method_is_called_with_empty_array() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->container->get([]);
    }

    #[Test]
    public function test_get_method_can_be_called_with_class_string() : void {
        $this->assertInstanceOf(\ArrayObject::class, $this->container->get(\ArrayObject::class));
    }

    #[Test]
    public function test_get_method_can_be_called_with_array_of_class_string() : void {
        $class_a = new class () {};
        $class_b = new class () {};
        $class_c = new class () {};

        $this->container->bind([$class_a::class, $class_b::class], $class_c::class);
        $this->assertInstanceOf($class_c::class, $this->container->get([$class_a::class, $class_b::class]));
    }

    #[Test]
    public function test_get_method_throws_EntryNotFoundException_if_no_resolver_for_intersection_type_is_found() : void {
        $class_a = new class () {};
        $class_b = new class () {};

        $this->expectException(EntryNotFoundException::class);
        $this->container->get([$class_a::class, $class_b::class]);
    }

    #[Test]
    public function test_explicit_singleton_binding_between_different_classes_dependency_gets_provided_by_get_method() : void {
        // A to B explicit singleton binding
        $class_a = new class () {};
        $class_b = new class () {};

        $this->container->singleton($class_a::class, $class_b::class);

        $this->assertInstanceOf($class_b::class, $this->container->get($class_a::class));
        $this->assertSame($this->container->get($class_a::class), $this->container->get($class_a::class));
    }

    #[Test]
    public function test_explicit_singleton_binding_dependency_gets_provided_by_get_method() : void {
        // A to A explicit singleton binding
        $class_a = new class () {};

        $this->container->singleton($class_a::class);

        $this->assertInstanceOf($class_a::class, $this->container->get($class_a::class));
        $this->assertSame($this->container->get($class_a::class), $this->container->get($class_a::class));
    }

    #[Test]
    public function test_explicit_singleton_binding_between_class_and_object_dependency_gets_provided_by_get_method() : void {
        // A to object explicit singleton binding
        $class_a = new class () {};

        $this->container->singleton($class_a::class, $class_a);

        $this->assertInstanceOf($class_a::class, $this->container->get($class_a::class));
        $this->assertSame($this->container->get($class_a::class), $this->container->get($class_a::class));
        $this->assertSame($class_a, $this->container->get($class_a::class));
    }

    #[Test]
    public function test_explicit_binding_between_different_classes_dependency_gets_provided_by_get_method() : void {
        // A to B explicit binding
        $class_a = new class () {};
        $class_b = new class () {};

        $this->container->bind($class_a::class, $class_b::class);

        $this->assertInstanceOf($class_b::class, $this->container->get($class_a::class));
        $this->assertNotSame($this->container->get($class_a::class), $this->container->get($class_a::class));
    }

    #[Test]
    public function test_explicit_binding_dependency_gets_provided_by_get_method() : void {
        //A to A explicit binding
        $class_a = new class () {};
        $this->container->bind($class_a::class);

        $this->assertInstanceOf($class_a::class, $this->container->get($class_a::class));
        $this->assertNotSame($this->container->get($class_a::class), $this->container->get($class_a::class));
    }

    #[Test]
    public function test_explicit_binding_between_class_and_closure_dependency_gets_provided_by_get_method() : void {
        //a to Closure explicit binding
        $class_a = new class () {};
        $class_b = new class () {};
        $class_b_class_string = $class_b::class;
        $this->container->bind($class_a::class, fn () => new $class_b_class_string());

        $this->assertInstanceOf($class_b::class, $this->container->get($class_a::class));
        $this->assertNotSame($this->container->get($class_a::class), $this->container->get($class_a::class));
    }

    #[Test]
    public function test_unbound_dependency_gets_auto_wired_and_provided_by_get_method() : void {
        //A to B autowire
        $class_a = new class () {};
        \Safe\class_alias($class_a::class, 'ClassA1');

        // @phpstan-ignore-next-line
        $class_b = new class ($class_a) {
            public function __construct(public readonly \ClassA1 $test_a) {
            }
        };

        $this->assertInstanceOf($class_b::class, $this->container->get($class_b::class));
        $this->assertInstanceOf($class_a::class, $this->container->get($class_b::class)->test_a);
        $this->assertNotSame($this->container->get($class_b::class), $this->container->get($class_b::class));
        $this->assertNotSame($this->container->get($class_b::class)->test_a, $this->container->get($class_b::class)->test_a);
    }

    #[Test]
    public function test_unbound_aliased_dependency_gets_auto_wired_and_provided_by_get_method() : void {
        $class_a = new class () {};

        $this->container->alias('test', $class_a::class);

        $this->assertInstanceOf($class_a::class, $this->container->get('test'));
        $this->assertNotSame($this->container->get('test'), $this->container->get('test'));
    }

    #[Test]
    public function test_bound_aliased_dependency_gets_resolved_and_provided_by_get_method() : void {
        $class_a = new class () {};
        $class_b = new class () {};

        $this->container->bind($class_a::class, $class_b::class);
        $this->container->alias('test', $class_a::class);

        $this->assertInstanceOf($class_b::class, $this->container->get('test'));
        $this->assertNotSame($this->container->get('test'), $this->container->get('test'));
    }

    #[Test]
    public function test_auto_wired_singleton_dependency_of_dependency_does_not_get_re_instantiated() : void {
        $class_a = new class () {};
        \Safe\class_alias($class_a::class, 'ClassA2');

        // @phpstan-ignore-next-line
        $class_b = new class ($class_a) {
            public function __construct(public readonly \ClassA2 $test_a) {
            }
        };

        // @phpstan-ignore-next-line
        $singleton = $this->container->singleton('ClassA2');

        $this->assertInstanceOf($class_b::class, $this->container->get($class_b::class));
        $this->assertInstanceOf($class_a::class, $this->container->get($class_b::class)->test_a);
        $this->assertSame($singleton, $this->container->get($class_b::class)->test_a);
        $this->assertNotSame($this->container->get($class_b::class), $this->container->get($class_b::class));
        $this->assertSame($this->container->get($class_b::class)->test_a, $this->container->get($class_b::class)->test_a);
    }

    #[Test]
    public function test_cant_autowire_a_non_existing_class() : void {
        $this->expectException(AutowiringFailureException::class);
        $this->container->get('non_existing_class');
    }

    #[Test]
    public function test_auto_wired_dependency_with_built_in_type_dependency_with_default_value_gets_resolved_using_the_default_value() : void {
        $class_a = new class () {
            public function __construct(public readonly int $test_a = 5) {
            }
        };

        $this->assertInstanceOf($class_a::class, $this->container->get($class_a::class));
        $this->assertSame(5, $this->container->get($class_a::class)->test_a);
    }

    #[Test]
    public function test_it_throws_AutowiringFailureException_if_the_dependency_is_dependant_on_a_built_in_type_and_no_default_value_is_provided() : void {
        $class_a = new class (0) {
            public function __construct(public readonly int $test_a) {
            }
        };

        $this->expectException(AutowiringFailureException::class);
        $this->container->get($class_a::class);
    }

    #[Test]
    public function test_it_tries_to_resolve_intersection_type_dependency_parameters() : void {
        $class_a = new class () {};
        \Safe\class_alias($class_a::class, 'ClassA3');

        // @phpstan-ignore-next-line
        $class_b = new class () extends \ClassA3 {};
        \Safe\class_alias($class_b::class, 'ClassB1');

        // @phpstan-ignore-next-line
        $class_c = new class ($class_b) {
            public function __construct(public readonly \ClassA3&\ClassB1 $test) {
            }
        };

        // @phpstan-ignore-next-line
        $class_d = new class () extends \ClassB1 {};

        // @phpstan-ignore-next-line
        $this->container->bind([\ClassA3::class, \ClassB1::class], $class_d::class);

        $this->assertInstanceOf($class_d::class, $this->container->get($class_c::class)->test);
    }

    #[Test]
    public function test_it_tries_to_resolve_union_type_dependency_parameters() : void {
        $class_a = new class () {};
        \Safe\class_alias($class_a::class, 'ClassA7');

        $class_b = new class () {};
        \Safe\class_alias($class_b::class, 'ClassB4');

        // @phpstan-ignore-next-line
        $class_c = new class ($class_a) {
            public function __construct(public readonly \ClassA7|\ClassB4 $test) {
            }
        };

        // @phpstan-ignore-next-line
        $this->assertInstanceOf('ClassA7', $this->container->get($class_a::class));
    }

    #[Test]
    public function test_auto_wired_dependency_with_non_type_hinted_dependency_with_default_value_gets_resolved_using_the_default_value() : void {
        $class_a = new class () {
            public function __construct(public $test_a = 5) {
            }
        };

        $this->assertInstanceOf($class_a::class, $this->container->get($class_a::class));
        $this->assertSame(5, $this->container->get($class_a::class)->test_a);
    }

    #[Test]
    public function test_it_throws_AutowiringFailureException_if_the_dependency_is_dependant_on_a_non_type_hinted_parameter_and_no_default_value_is_provided() : void {
        $class_a = new class (0) {
            public function __construct(public $test_a) {
            }
        };

        $this->expectException(AutowiringFailureException::class);
        $this->container->get($class_a::class);
    }

    // @phpstan-ignore-next-line
    public static function container_configurations_provider() : array {
        $class_a = new class () {};
        \Safe\class_alias($class_a::class, 'ClassA5');

        // @phpstan-ignore-next-line
        $class_b = new class ($class_a) {
            public function __construct(public readonly \ClassA5 $classA5) {
            }
        };
        \Safe\class_alias($class_b::class, 'ClassB3');

        return [[[
            'singletons' => [$class_b::class, $class_a::class],
        ]]];
    }

    /** @param ContainerConfiguration $configurations */
    #[Test]
    #[DataProvider('container_configurations_provider')]
    public function test_it_tries_to_resolve_dependency_parameters_from_configs(array $configurations) : void {
        $container_configurator = $this->createPartialMock(ContainerConfigurator::class, ['resolveDependencyFromConfigs']);

        $container_configurator->expects($this->once())->method('resolveDependencyFromConfigs')->willReturn(new $configurations['singletons'][1]());

        $container_configurator->__construct($configurations);

        $this->container->useConfigurator($container_configurator);

        // @phpstan-ignore-next-line
        $this->assertSame($this->container->get($configurations['singletons'][0]), $this->container->get($configurations['singletons'][0]));

        // @phpstan-ignore-next-line
        $this->assertSame($this->container->get($configurations['singletons'][1]), $this->container->get($configurations['singletons'][1]));
    }

    #[Test]
    public function test_it_tries_to_resolve_dependency_parameters_from_annotation() : void {
        $this->container->bind(\ArrayObject::class, fn () => []);

        $class_a = new class (new \ArrayObject()) {
            /** @param \ArrayObject $test */
            public function __construct(public $test) {
            }
        };

        \Safe\class_alias($class_a::class, 'ClassA6');

        $this->assertSame([], $this->container->get($class_a::class)->test);
        // @phpstan-ignore-next-line
        $this->assertInstanceOf('ClassA6', $this->container->get($class_a::class));
    }

    #[Test]
    public function test_it_tries_to_use_default_value_for_dependency_parameters_if_annotation_does_not_contain_type_hint() : void {
        $class_a = new class () {
            /** not type hinted */
            public function __construct(public $test = 'test') {
            }
        };

        $this->assertIsString($this->container->get($class_a::class)->test);
        $this->assertSame('test', $this->container->get($class_a::class)->test);
    }

    #[Test]
    public function test_it_throws_AutowiringFailureException_for_dependency_parameters_without_type_hint_nor_annotation_nor_default_value() : void {
        $class_a = new class ('test') {
            /** not type hinted */
            public function __construct(public $test) {
            }
        };

        $this->expectException(AutowiringFailureException::class);

        $this->container->get($class_a::class);
    }

    public function test_has_method_returns_true_if_container_has_an_entry() : void {
        $class_a = new class () {};

        $this->container->bind($class_a::class);

        $this->assertTrue($this->container->has($class_a::class));
    }

    public function test_has_method_returns_false_if_container_does_not_have_an_entry() : void {
        $class_a = new class () {};

        $this->assertFalse($this->container->has($class_a::class));
    }

    public function test_has_method_returns_true_if_container_has_a_singletone() : void {
        $class_a = new class () {};

        $this->container->singleton($class_a::class);

        $this->assertTrue($this->container->has($class_a::class));
    }

    public function test_has_method_returns_false_if_container_does_not_have_a_singletone() : void {
        $class_a = new class () {};

        $this->assertFalse($this->container->has($class_a::class));
    }

    public function test_has_method_returns_true_if_container_has_an_intersection_type_resolver() : void {
        $class_a = new class () {};
        $class_b = new class () {};
        $class_c = new class () {};

        $this->container->bind([$class_a::class, $class_b::class], $class_c::class);

        $this->assertTrue($this->container->has([$class_a::class, $class_b::class]));
    }

    public function test_has_method_returns_false_if_container_does_not_have_an_intersection_type_resolver() : void {
        $class_a = new class () {};
        $class_b = new class () {};

        $this->assertFalse($this->container->has([$class_a::class, $class_b::class]));
    }

    public function test_hasIntersectionTypeResolver_method_returns_true_if_container_has_an_intersection_type_resolver() : void {
        $class_a = new class () {};
        $class_b = new class () {};
        $class_c = new class () {};

        $this->container->bind([$class_a::class, $class_b::class], $class_c::class);

        $this->assertTrue($this->container->hasIntersectionTypeResolver([$class_a::class, $class_b::class]));
    }

    public function test_hasIntersectionTypeResolver_method_returns_false_if_container_does_not_have_an_intersection_type_resolver() : void {
        $class_a = new class () {};
        $class_b = new class () {};

        $this->assertFalse($this->container->hasIntersectionTypeResolver([$class_a::class, $class_b::class]));
    }

    public function test_hasEntry_method_returns_true_if_container_has_an_entry() : void {
        $class_a = new class () {};

        $this->container->bind($class_a::class);

        $this->assertTrue($this->container->hasEntry($class_a::class));
    }

    public function test_hasEntry_method_returns_false_if_container_does_not_have_an_entry() : void {
        $class_a = new class () {};

        $this->assertFalse($this->container->hasEntry($class_a::class));
    }

    public function test_hasSingleton_method_returns_true_if_container_has_a_singleton() : void {
        $class_a = new class () {};

        $this->container->singleton($class_a::class);

        $this->assertTrue($this->container->hasSingleton($class_a::class));
    }

    public function test_hasSingleton_method_returns_false_if_container_does_not_have_a_singleton() : void {
        $class_a = new class () {};

        $this->assertFalse($this->container->hasSingleton($class_a::class));
    }

    public function test_hasAlias_method_returns_true_if_container_has_a_alias() : void {
        $class_a = new class () {};

        $this->container->alias('test', $class_a::class);

        $this->assertTrue($this->container->hasAlias('test'));
    }

    public function test_hasAlias_method_returns_false_if_container_does_not_have_a_alias() : void {
        $this->assertFalse($this->container->hasAlias('test'));
    }

    public function test_clear_method_clears_container() : void {
        $class_1 = new class () {};
        $class_2 = new class () {};
        $class_3 = new class () {};
        $class_4 = new class () {};
        $class_5 = new class () {};
        $class_6 = new class () {};
        $class_7 = new class () {};

        $this->container->bind($class_1::class);
        $this->container->bind($class_2::class);

        $this->container->singleton($class_3::class);
        $this->container->singleton($class_4::class);

        $this->container->bind([$class_5::class, $class_6::class], $class_7::class);

        $this->assertTrue($this->container->has($class_1::class));
        $this->assertTrue($this->container->has($class_2::class));
        $this->assertTrue($this->container->has($class_3::class));
        $this->assertTrue($this->container->has($class_4::class));
        $this->assertTrue($this->container->has([$class_5::class, $class_6::class]));

        $this->container->clear();

        $this->assertFalse($this->container->has($class_1::class));
        $this->assertFalse($this->container->has($class_2::class));
        $this->assertFalse($this->container->has($class_3::class));
        $this->assertFalse($this->container->has($class_4::class));
        $this->assertFalse($this->container->has([$class_5::class, $class_6::class]));
    }

    public function test_when_container_use_configurator_it_calls_configure_method_on_configurator_passing_itself_as_a_parameter() : void {
        $configurator = $this->createMock(ContainerConfigurator::class);
        $configurator->expects($this->once())->method('configure')->with($this->container);

        $this->container->useConfigurator($configurator);
    }

    public function test_it_detects_circular_reference() : void {
        $class_a = new class ('test') {
            public function __construct(public readonly \ClassB5|string $test) {
            }
        };
        \Safe\class_alias($class_a::class, 'ClassA8');

        $class_b = new class ('test') {
            public function __construct(public readonly \ClassA8|string $test) {
            }
        };
        \Safe\class_alias($class_b::class, 'ClassB5');

        try {
            $this->container->get($class_a::class);
        } catch (Exception $e) {
            $original_exception = $e;

            while (true) {
                if (is_null($original_exception->getPrevious())) {
                    break;
                }

                $original_exception = $original_exception->getPrevious();
            }

            $this->assertEquals(CircularReferenceException::class, get_class($original_exception));
        }
    }

    #[Test]
    public function test_it_call_given_lambda_callable(): void {
        $callable = fn() => 'test';

        $this->assertEquals('test', $this->container->call($callable));
    }
    
    #[Test]
    public function test_it_call_given_lambda_callable_with_given_params(): void {
        $callable = fn(string $name) => "hello {$name}";

        $this->assertEquals('hello test', $this->container->call($callable, ['name'=>'test']));
    }
    
    #[Test]
    public function test_it_call_given_function_string_callable(): void {
        $callable = 'phpversion';

        $this->assertStringContainsString(PHP_VERSION, $this->container->call($callable));
    }
    
    #[Test]
    public function test_it_call_given_function_string_callable_with_given_params(): void {
        $callable = 'implode';

        $this->assertStringContainsString('test,test', $this->container->call($callable, [",", ["test", "test"]]));
    }
    
    #[Test]
    public function test_it_call_given_static_method_string_callable(): void {
        $class = new class() {
            public static function test(): string {return "test";}
        };
        \Safe\class_alias($class::class, 'ClassA9');

        // @phpstan-ignore-next-line
        $this->assertEquals("test", $this->container->call(\ClassA9::class."::test"));
    }
    
    #[Test]
    public function test_it_call_given_static_method_string_callable_with_given_params(): void {
        $class = new class() {
            public static function hello(string $name): string {return "hello {$name}";}
        };
        \Safe\class_alias($class::class, 'ClassA10');

        // @phpstan-ignore-next-line
        $this->assertEquals("hello test", $this->container->call(\ClassA10::class."::hello", ['name'=>'test']));
    }
    
    #[Test]
    public function test_it_call_given_method_string_callable(): void {
        $class = new class() {
            public function test(): string {return "test";}
        };
        \Safe\class_alias($class::class, 'ClassA11');

        // @phpstan-ignore-next-line
        $this->assertEquals("test", $this->container->call(\ClassA11::class."@test"));
    }
    
    #[Test]
    public function test_it_call_given_method_string_callable_with_given_params(): void {
        $class = new class() {
            public function hello(string $name): string {return "hello {$name}";}
        };
        \Safe\class_alias($class::class, 'ClassA12');

        // @phpstan-ignore-next-line
        $this->assertEquals("hello test", $this->container->call(\ClassA12::class."@hello", ['name'=>'test']));
    }
    
    #[Test]
    public function test_it_throws_InvalidArgumentException_if_params_is_not_an_associative_array(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("\$param array parameter passed to call should be an associative array, where the keys are the parameter name.");

        $this->container->call([new \ArrayObject(), 'append'], ['test']);
    }
    
    #[Test]
    public function test_it_does_not_throw_if_params_is_an_associative_array(): void {
        $arr_obj = new \ArrayObject();
        $this->container->call([$arr_obj, 'append'], ['value'=>'test']);
        $this->assertContains('test', $arr_obj) ;
    }

    // @phpstan-ignore-next-line
    public static function callable_array_with_invalid_method_provider() : array {
        return [
            [[new \ArrayObject(), 'test']],
            [[\ArrayObject::class, 'test']]
        ];
    }

    /** @param array{string|object, string} $callable */
    #[Test]
    #[DataProvider('callable_array_with_invalid_method_provider')]
    public function test_if_callable_is_array_and_method_does_not_exists_it_throws_BadMethodCallException(array $callable): void {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage("Container is unable to call method test as it does not exists in class ".\ArrayObject::class);

        $this->container->call($callable);
    }

    #[Test]
    public function test_it_calls_array_callable_method_considering_annotations(): void {
        $class = new class{
            /** @param \ArrayObject $array */
            public function count($array):int {
                $array->append("test");

                return $array->count();
            }
        };
        \Safe\class_alias($class::class, 'ClassA13');

        // @phpstan-ignore-next-line
        $this->assertEquals(1, $this->container->call([new \ClassA13, 'count']));
    }

    #[Test]
    public function test_it_calls_array_callable_method_autowiring_dependencies(): void {
        $class = new class{
            public function count(\ArrayObject $array):int {
                $array->append("test");

                return $array->count();
            }
        };
        \Safe\class_alias($class::class, 'ClassA14');

        // @phpstan-ignore-next-line
        $this->assertEquals(1, $this->container->call([new \ClassA14, 'count']));
    }

    #[Test]
    public function test_it_calls_array_callable_method_autowiring_dependencies_without_overriding_user_defined_params(): void {
        $class = new class{
            public function count(\ArrayObject $array):int {
                $array->append("test");

                return $array->count();
            }
        };
        \Safe\class_alias($class::class, 'ClassA15');

        // @phpstan-ignore-next-line
        $this->assertEquals(2, $this->container->call([new \ClassA15, 'count'], ['array' => new \ArrayObject(['test0'])]));
    }

    #[Test]
    public function test_it_calls_lambda_callable_autowiring_dependencies(): void {
        $callable = function(\ArrayObject $array) {$array->append("test"); return $array->count();};

        $this->assertEquals(1, $this->container->call($callable));
    }

    #[Test]
    public function test_it_calls_lambda_callable_autowiring_dependencies_without_overriding_user_defined_params(): void {
        $callable = function(\ArrayObject $array) {$array->append("test"); return $array->count();};

        $this->assertEquals(2, $this->container->call($callable, ['array' => new \ArrayObject(['test0'])]));
    }
}
