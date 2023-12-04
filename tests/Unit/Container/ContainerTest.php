<?php

namespace Spin8\Tests\Unit;

use Error;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Spin8\Container\Configuration\ContainerConfigurator;
use Spin8\Container\Container;
use Spin8\Container\Exceptions\AutowiringFailureException;
use Spin8\Container\Exceptions\EntryNotFoundException;

/** @phpstan-import-type ContainerConfiguration from \Spin8\Container\Configuration\AbstractContainerConfigurator */
#[CoversClass(Container::class)]
final class ContainerTest extends \PHPUnit\Framework\TestCase {

    protected Container $container;

    public function setUp(): void {
        parent::setUp();
        
        $this->container = new Container();
    }

    public function tearDown(): void {
        unset($this->container);
        
        parent::tearDown();
    }




    #[Test]
    public function test_throws_InvalidArgumentException_if_get_method_is_called_with_empty_string(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->container->get('');
    }

    #[Test]
    public function test_throws_InvalidArgumentException_if_get_method_is_called_with_empty_array(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->container->get([]);
    }

    #[Test]
    public function test_get_method_can_be_called_with_class_string(): void {
        $this->assertInstanceOf(\ArrayObject::class, $this->container->get(\ArrayObject::class));        
    }

    #[Test]
    public function test_get_method_can_be_called_with_array_of_class_string(): void {
        $class_a = new class{};
        $class_b = new class{};
        $class_c = new class{};
        
        $this->container->bind([$class_a::class, $class_b::class], $class_c::class);
        $this->assertInstanceOf($class_c::class, $this->container->get([$class_a::class, $class_b::class]));
    }

    #[Test]
    public function test_get_method_throws_EntryNotFoundException_if_no_resolver_for_intersection_type_is_found(): void {
        $class_a = new class{};
        $class_b = new class{};
        
        $this->expectException(EntryNotFoundException::class);
        $this->container->get([$class_a::class, $class_b::class]);
    }

    #[Test]
    public function test_explicit_singleton_binding_between_different_classes_dependency_gets_provided_by_get_method(): void {
        // A to B explicit singleton binding
        $class_a = new class{};
        $class_b = new class{};

        $this->container->singleton($class_a::class, $class_b::class);

        $this->assertInstanceOf($class_b::class, $this->container->get($class_a::class));
        $this->assertSame($this->container->get($class_a::class), $this->container->get($class_a::class));
    }

    #[Test]
    public function test_explicit_singleton_binding_dependency_gets_provided_by_get_method(): void {
        // A to A explicit singleton binding
        $class_a = new class{};

        $this->container->singleton($class_a::class);

        $this->assertInstanceOf($class_a::class, $this->container->get($class_a::class));
        $this->assertSame($this->container->get($class_a::class), $this->container->get($class_a::class));
    }

    #[Test]
    public function test_explicit_singleton_binding_between_class_and_object_dependency_gets_provided_by_get_method(): void {
        // A to object explicit singleton binding
        $class_a = new class{};

        $this->container->singleton($class_a::class, $class_a);

        $this->assertInstanceOf($class_a::class, $this->container->get($class_a::class));
        $this->assertSame($this->container->get($class_a::class), $this->container->get($class_a::class));
        $this->assertSame($class_a, $this->container->get($class_a::class));
    }

    #[Test]
    public function test_explicit_binding_between_different_classes_dependency_gets_provided_by_get_method(): void {
        // A to B explicit binding
        $class_a = new class{};
        $class_b = new class{};

        $this->container->bind($class_a::class, $class_b::class);

        $this->assertInstanceOf($class_b::class, $this->container->get($class_a::class));
        $this->assertNotSame($this->container->get($class_a::class), $this->container->get($class_a::class));
    }

    #[Test]
    public function test_explicit_binding_dependency_gets_provided_by_get_method(): void {
        //A to A explicit binding
        $class_a= new class{};
        $this->container->bind($class_a::class);

        $this->assertInstanceOf($class_a::class, $this->container->get($class_a::class));
        $this->assertNotSame($this->container->get($class_a::class), $this->container->get($class_a::class));
    }

    #[Test]
    public function test_explicit_binding_between_class_and_closure_dependency_gets_provided_by_get_method(): void {
        //a to Closure explicit binding
        $class_a = new class{};
        $class_b = new class{};
        $class_b_class_string = $class_b::class;
        $this->container->bind($class_a::class, fn() => new $class_b_class_string());

        $this->assertInstanceOf($class_b::class, $this->container->get($class_a::class));
        $this->assertNotSame($this->container->get($class_a::class), $this->container->get($class_a::class));
    }

    #[Test]
    public function test_unbound_dependency_gets_auto_wired_and_provided_by_get_method(): void {
        //A to B autowire
        $class_a = new class{};
        \Safe\class_alias($class_a::class, "ClassA1");
        // @phpstan-ignore-next-line
        $class_b = new class($class_a){function __construct(public readonly \ClassA1 $test_a){}};

        $this->assertInstanceOf($class_b::class, $this->container->get($class_b::class));
        $this->assertInstanceOf($class_a::class, $this->container->get($class_b::class)->test_a);
        $this->assertNotSame($this->container->get($class_b::class), $this->container->get($class_b::class));
        $this->assertNotSame($this->container->get($class_b::class)->test_a, $this->container->get($class_b::class)->test_a);
    }

    #[Test]
    public function test_unbound_aliased_dependency_gets_auto_wired_and_provided_by_get_method(): void {        
        $class_a = new class{};
        
        $this->container->alias("test", $class_a::class);

        $this->assertInstanceOf($class_a::class, $this->container->get("test"));
        $this->assertNotSame($this->container->get("test"), $this->container->get("test"));
    }

    #[Test]
    public function test_bound_aliased_dependency_gets_resolved_and_provided_by_get_method(): void {        
        $class_a = new class{};
        $class_b = new class{};
        
        $this->container->bind($class_a::class, $class_b::class);
        $this->container->alias("test", $class_a::class);

        $this->assertInstanceOf($class_b::class, $this->container->get("test"));
        $this->assertNotSame($this->container->get("test"), $this->container->get("test"));
    }

    #[Test]
    public function test_auto_wired_singleton_dependency_of_dependency_does_not_get_re_instantiated(): void {        
        $class_a = new class{};
        \Safe\class_alias($class_a::class, "ClassA2");
        // @phpstan-ignore-next-line
        $class_b = new class($class_a){function __construct(public readonly \ClassA2 $test_a){}};

        // @phpstan-ignore-next-line
        $singleton = $this->container->singleton('ClassA2');

        $this->assertInstanceOf($class_b::class, $this->container->get($class_b::class));
        $this->assertInstanceOf($class_a::class, $this->container->get($class_b::class)->test_a);
        $this->assertSame($singleton, $this->container->get($class_b::class)->test_a);
        $this->assertNotSame($this->container->get($class_b::class), $this->container->get($class_b::class));
        $this->assertSame($this->container->get($class_b::class)->test_a, $this->container->get($class_b::class)->test_a);
    }

    #[Test]
    public function test_cant_autowire_a_non_existing_class(): void {
        $this->expectException(AutowiringFailureException::class);
        $this->container->get('non_existing_class');
    }

    #[Test]
    public function test_auto_wired_dependency_with_built_in_type_dependency_with_default_value_gets_resolved_using_the_default_value(): void {
        $class_a = new class{function __construct(public readonly int $test_a = 5){}};
        
        $this->assertInstanceOf($class_a::class, $this->container->get($class_a::class));
        $this->assertSame(5, $this->container->get($class_a::class)->test_a);
    }

    #[Test]
    public function test_it_throws_AutowiringFailureException_if_the_dependency_is_dependant_on_a_built_in_type_and_no_default_value_is_provided(): void {
        $class_a = new class(0){function __construct(public readonly int $test_a){}};
        
        $this->expectException(AutowiringFailureException::class);
        $this->container->get($class_a::class);
    }

    #[Test]
    public function test_it_tries_to_resolve_intersection_type_dependency_parameters(): void {
        $class_a = new class{};
        \Safe\class_alias($class_a::class, "ClassA3");

        // @phpstan-ignore-next-line
        $class_b = new class extends \ClassA3{};
        \Safe\class_alias($class_b::class, "ClassB1");

        // @phpstan-ignore-next-line
        $class_c = new class($class_b){function __construct(public readonly \ClassA3&\ClassB1 $test){}};

        // @phpstan-ignore-next-line
        $class_d = new class extends \ClassB1{};

        // @phpstan-ignore-next-line
        $this->container->bind([\ClassA3::class, \ClassB1::class], $class_d::class);
        
        $this->assertInstanceOf($class_d::class, $this->container->get($class_c::class)->test);
    }

    #[Test]
    public function test_it_tries_to_resolve_union_type_dependency_parameters(): void {           
        $class_a = new class{};
        \Safe\class_alias($class_a::class, "ClassA7");

        $class_b = new class(){};
        \Safe\class_alias($class_b::class, "ClassB4");

        // @phpstan-ignore-next-line
        $class_c = new class($class_a){
            function __construct(public readonly \ClassA7|\ClassB4 $test) {}
        };  
        
        // @phpstan-ignore-next-line
        $this->assertInstanceOf("ClassA7", $this->container->get($class_a::class));
    }

    #[Test]
    public function test_auto_wired_dependency_with_non_type_hinted_dependency_with_default_value_gets_resolved_using_the_default_value(): void {
        $class_a = new class{function __construct(public $test_a = 5){}};
        
        $this->assertInstanceOf($class_a::class, $this->container->get($class_a::class));
        $this->assertSame(5, $this->container->get($class_a::class)->test_a);
    }
    
    #[Test]
    public function test_it_throws_AutowiringFailureException_if_the_dependency_is_dependant_on_a_non_type_hinted_parameter_and_no_default_value_is_provided(): void {
        $class_a = new class(0){function __construct(public $test_a){}};
        
        $this->expectException(AutowiringFailureException::class);
        $this->container->get($class_a::class);
    }
    
    // @phpstan-ignore-next-line
    public static function conatiner_configurations_provider(): array{
        $class_a = new class{};
        \Safe\class_alias($class_a::class, "ClassA5");

        // @phpstan-ignore-next-line
        $class_b = new class($class_a){function __construct(public readonly \ClassA5 $classA5){}};
        \Safe\class_alias($class_b::class, "ClassB3");

        return [[[
            "singletons" => [$class_b::class, $class_a::class]
        ]]];
    }

    /** @param ContainerConfiguration $configurations */
    #[Test]
    #[DataProvider('conatiner_configurations_provider')]
    public function test_it_tries_to_resolve_dependency_parameters_from_configs(array $configurations): void {        
        $container_configurator = $this->createPartialMock(ContainerConfigurator::class, ['resolveDependencyFromConfigs']);

        $container_configurator->expects($this->once())->method('resolveDependencyFromConfigs')->willReturn(new $configurations['singletons'][1]);

        $container_configurator->__construct($configurations);
        
        $this->container->useConfigurator($container_configurator);

        // @phpstan-ignore-next-line
        $this->assertSame($this->container->get($configurations['singletons'][0]), $this->container->get($configurations['singletons'][0]));

        // @phpstan-ignore-next-line
        $this->assertSame($this->container->get($configurations['singletons'][1]), $this->container->get($configurations['singletons'][1]));
    }

    #[Test]
    public function test_it_tries_to_resolve_dependency_parameters_from_annotation(): void {   
        $this->container->bind(\ArrayObject::class, fn() => []);
        
        $class_a = new class(new \ArrayObject()){
            /** @param \ArrayObject $test */
            function __construct(public $test){}
        };

        \Safe\class_alias($class_a::class, "ClassA6");

        $this->assertSame([], $this->container->get($class_a::class)->test);
        // @phpstan-ignore-next-line
        $this->assertInstanceOf("ClassA6", $this->container->get($class_a::class));        
    }

    #[Test]
    public function test_it_tries_to_use_default_value_for_dependency_parameters_if_annotation_does_not_contain_type_hint(): void {
        $class_a = new class{
            /** not type hinted */
            function __construct(public $test = "test"){}
        };

        $this->assertIsString($this->container->get($class_a::class)->test);
        $this->assertSame("test", $this->container->get($class_a::class)->test);
    }

    #[Test]
    public function test_it_throws_AutowiringFailureException_for_dependency_parameters_without_type_hint_nor_annotation_nor_default_value(): void {
        $class_a = new class("test"){
            /** not type hinted */
            function __construct(public $test){}
        };

        $this->expectException(AutowiringFailureException::class);

        $this->container->get($class_a::class);        
    }

    public function test_has_method_returns_true_if_container_has_an_entry(): void {
        $class_a = new class{};
        
        $this->container->bind($class_a::class);

        $this->assertTrue($this->container->has($class_a::class));
    }

    public function test_has_method_returns_false_if_container_does_not_have_an_entry(): void {
        $class_a = new class{};

        $this->assertFalse($this->container->has($class_a::class));
    }

    public function test_has_method_returns_true_if_container_has_a_singletone(): void {
        $class_a = new class{};
        
        $this->container->singleton($class_a::class);

        $this->assertTrue($this->container->has($class_a::class));
    }

    public function test_has_method_returns_false_if_container_does_not_have_a_singletone(): void {
        $class_a = new class{};
        
        $this->assertFalse($this->container->has($class_a::class));
    }

    public function test_has_method_returns_true_if_container_has_an_intersection_type_resolver(): void {
        $class_a = new class{};
        $class_b = new class{};
        $class_c = new class{};
        
        $this->container->bind([$class_a::class, $class_b::class], $class_c::class);

        $this->assertTrue($this->container->has([$class_a::class, $class_b::class]));
    }

    public function test_has_method_returns_false_if_container_does_not_have_an_intersection_type_resolver(): void {
        $class_a = new class{};
        $class_b = new class{};
        
        $this->assertFalse($this->container->has([$class_a::class, $class_b::class]));
    }

    public function test_hasIntersectionTypeResolver_method_returns_true_if_container_has_an_intersection_type_resolver(): void {
        $class_a = new class{};
        $class_b = new class{};
        $class_c = new class{};
        
        $this->container->bind([$class_a::class, $class_b::class], $class_c::class);

        $this->assertTrue($this->container->hasIntersectionTypeResolver([$class_a::class, $class_b::class]));
    }

    public function test_hasIntersectionTypeResolver_method_returns_false_if_container_does_not_have_an_intersection_type_resolver(): void {
        $class_a = new class{};
        $class_b = new class{};
        
        $this->assertFalse($this->container->hasIntersectionTypeResolver([$class_a::class, $class_b::class]));
    }

    public function test_hasEntry_method_returns_true_if_container_has_an_entry(): void {
        $class_a = new class{};
    
        $this->container->bind($class_a::class);

        $this->assertTrue($this->container->hasEntry($class_a::class));
    }

    public function test_hasEntry_method_returns_false_if_container_does_not_have_an_entry(): void {
        $class_a = new class{};
        
        $this->assertFalse($this->container->hasEntry($class_a::class));
    }

    public function test_hasSingleton_method_returns_true_if_container_has_a_singleton(): void {
        $class_a = new class{};
    
        $this->container->singleton($class_a::class);

        $this->assertTrue($this->container->hasSingleton($class_a::class));
    }

    public function test_hasSingleton_method_returns_false_if_container_does_not_have_a_singleton(): void {
        $class_a = new class{};
        
        $this->assertFalse($this->container->hasSingleton($class_a::class));
    }

    public function test_hasAlias_method_returns_true_if_container_has_a_alias(): void {
        $class_a = new class{};
    
        $this->container->alias("test", $class_a::class);

        $this->assertTrue($this->container->hasAlias("test"));
    }

    public function test_hasAlias_method_returns_false_if_container_does_not_have_a_alias(): void {        
        $this->assertFalse($this->container->hasAlias("test"));
    }

}
