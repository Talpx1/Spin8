<?php

namespace Spin8\Tests\Unit;

use Error;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Spin8\Container\Container;
use Spin8\Container\Exceptions\AutowiringFailureException;

#[CoversClass(Container::class)]
final class ContainerTest extends \PHPUnit\Framework\TestCase {

    #[Test]
    public function test_container_instance_accepts_configurations(): void {
        // @phpstan-ignore-next-line
        $container = new Container(['test']);
        $this->assertSame(['test'], $container->getConfigurations());
    }

    #[Test]
    public function test_if_no_configurations_are_passed_to_the_container_instance_configurations_are_null(): void {
        $container = new Container();
        $this->assertNull($container->getConfigurations());
    }

    /** @param mixed[] $configs */
    #[TestWith([[123]])]
    #[TestWith([['test']])]
    #[Test]
    public function test_can_get_configurations_via_getConfigurations_method(array $configs): void {
        // @phpstan-ignore-next-line
        $container = new Container($configs);
        $this->assertSame($configs, $container->getConfigurations());
    }

    #[Test]
    public function test_can_set_is_loading_configurations_via_setIsLoadingConfigurations_method(): void {
        $container = new Container();

        $this->assertFalse($container->isLoadingConfigurations());
        
        $container->setIsLoadingConfigurations(true);
        $this->assertTrue($container->isLoadingConfigurations());
        
        $container->setIsLoadingConfigurations(false);
        $this->assertFalse($container->isLoadingConfigurations());
    }

    #[Test]
    public function test_throws_InvalidArgumentException_if_get_method_is_called_with_empty_string(): void {
        $container = new Container();

        $this->expectException(InvalidArgumentException::class);
        $container->get('');
    }

    #[Test]
    public function test_explicit_singleton_binding_between_different_classes_dependency_gets_provided_by_get_method(): void {
        $container = new Container();

        // A to B explicit singleton binding
        $class_a = new class{};
        $class_b = new class{};

        $container->singleton($class_a::class, $class_b::class);

        $this->assertInstanceOf($class_b::class, $container->get($class_a::class));
        $this->assertSame($container->get($class_a::class), $container->get($class_a::class));
    }

    #[Test]
    public function test_explicit_singleton_binding_dependency_gets_provided_by_get_method(): void {
        $container = new Container();

        // A to A explicit singleton binding
        $class_a = new class{};

        $container->singleton($class_a::class);

        $this->assertInstanceOf($class_a::class, $container->get($class_a::class));
        $this->assertSame($container->get($class_a::class), $container->get($class_a::class));
    }

    #[Test]
    public function test_explicit_singleton_binding_between_class_and_object_dependency_gets_provided_by_get_method(): void {
        $container = new Container();

        // A to object explicit singleton binding
        $class_a = new class{};

        $container->singleton($class_a::class, $class_a);

        $this->assertInstanceOf($class_a::class, $container->get($class_a::class));
        $this->assertSame($container->get($class_a::class), $container->get($class_a::class));
        $this->assertSame($class_a, $container->get($class_a::class));
    }

    #[Test]
    public function test_explicit_binding_between_different_classes_dependency_gets_provided_by_get_method(): void {
        $container = new Container();

        // A to B explicit binding
        $class_a = new class{};
        $class_b = new class{};

        $container->bind($class_a::class, $class_b::class);

        $this->assertInstanceOf($class_b::class, $container->get($class_a::class));
        $this->assertNotSame($container->get($class_a::class), $container->get($class_a::class));
    }

    #[Test]
    public function test_explicit_binding_dependency_gets_provided_by_get_method(): void {
        $container = new Container();

        //A to A explicit binding
        $class_a= new class{};
        $container->bind($class_a::class);

        $this->assertInstanceOf($class_a::class, $container->get($class_a::class));
        $this->assertNotSame($container->get($class_a::class), $container->get($class_a::class));
    }

    #[Test]
    public function test_explicit_binding_between_class_and_closure_dependency_gets_provided_by_get_method(): void {
        $container = new Container();

        //a to Closure explicit binding
        $class_a = new class{};
        $class_b = new class{};
        $class_b_class_string = $class_b::class;
        $container->bind($class_a::class, fn() => new $class_b_class_string());

        $this->assertInstanceOf($class_b::class, $container->get($class_a::class));
        $this->assertNotSame($container->get($class_a::class), $container->get($class_a::class));
    }

    #[Test]
    public function test_unbound_dependency_gets_auto_wired_and_provided_by_get_method(): void {
        $container = new Container();

        //A to B autowire
        $class_a = new class{};
        \Safe\class_alias($class_a::class, "ClassA1");
        // @phpstan-ignore-next-line
        $class_b = new class($class_a){function __construct(public readonly \ClassA1 $test_a){}};

        $this->assertInstanceOf($class_b::class, $container->get($class_b::class));
        $this->assertInstanceOf($class_a::class, $container->get($class_b::class)->test_a);
        $this->assertNotSame($container->get($class_b::class), $container->get($class_b::class));
        $this->assertNotSame($container->get($class_b::class)->test_a, $container->get($class_b::class)->test_a);
    }

    #[Test]
    public function test_unbound_aliased_dependency_gets_auto_wired_and_provided_by_get_method(): void {
        $container = new Container();
        
        $class_a = new class{};
        
        $container->alias("test", $class_a::class);

        $this->assertInstanceOf($class_a::class, $container->get("test"));
        $this->assertNotSame($container->get("test"), $container->get("test"));
    }

    #[Test]
    public function test_bound_aliased_dependency_gets_resolved_and_provided_by_get_method(): void {
        $container = new Container();
        
        $class_a = new class{};
        $class_b = new class{};
        
        $container->bind($class_a::class, $class_b::class);
        $container->alias("test", $class_a::class);

        $this->assertInstanceOf($class_b::class, $container->get("test"));
        $this->assertNotSame($container->get("test"), $container->get("test"));
    }

    #[Test]
    public function test_auto_wired_singleton_dependency_of_dependency_does_not_get_re_instantiated(): void {
        $container = new Container();
        
        $class_a = new class{};
        \Safe\class_alias($class_a::class, "ClassA2");
        // @phpstan-ignore-next-line
        $class_b = new class($class_a){function __construct(public readonly \ClassA2 $test_a){}};

        // @phpstan-ignore-next-line
        $singleton = $container->singleton('ClassA2');

        $this->assertInstanceOf($class_b::class, $container->get($class_b::class));
        $this->assertInstanceOf($class_a::class, $container->get($class_b::class)->test_a);
        $this->assertSame($singleton, $container->get($class_b::class)->test_a);
        $this->assertNotSame($container->get($class_b::class), $container->get($class_b::class));
        $this->assertSame($container->get($class_b::class)->test_a, $container->get($class_b::class)->test_a);
    }

    #[Test]
    public function test_cant_autowire_a_non_existing_class(): void {
        $container = new Container();

        $this->expectException(AutowiringFailureException::class);
        $container->get('non-existing_class');
    }

    #[Test]
    public function test_auto_wired_dependency_with_built_in_type_dependency_with_default_value_gets_resolved_using_the_default_value(): void {
        $container = new Container();

        $class_a = new class{function __construct(public readonly int $test_a = 5){}};
        
        $this->assertInstanceOf($class_a::class, $container->get($class_a::class));
        $this->assertSame(5, $container->get($class_a::class)->test_a);
    }

    #[Test]
    public function test_it_throws_AutowiringFailureException_if_the_dependency_is_dependant_on_a_built_in_type_and_no_default_value_is_provided(): void {
        $container = new Container();

        $class_a = new class(0){function __construct(public readonly int $test_a){}};
        
        $this->expectException(AutowiringFailureException::class);
        $container->get($class_a::class);
    }

    #[Test]
    public function test_it_throws_AutowiringFailureException_if_the_dependency_is_dependant_on_an_intersection_type(): void {
        $container = new Container();

        $class_a = new class{};
        \Safe\class_alias($class_a::class, "ClassA3");

        $class_b = new class extends \ClassA3{};
        \Safe\class_alias($class_b::class, "ClassB1");

        // @phpstan-ignore-next-line
        $class_c = new class($class_b){function __construct(public readonly \ClassA3&\ClassB1 $test_a){}};
        
        $this->expectException(AutowiringFailureException::class);
        $container->get($class_c::class);
    }

    #[Test]
    public function test_it_throws_AutowiringFailureException_if_the_dependency_is_dependant_on_an_union_type(): void {
        $container = new Container();

        $class_a = new class{};
        \Safe\class_alias($class_a::class, "ClassA4");

        $class_b = new class{};
        \Safe\class_alias($class_b::class, "ClassB2");

        // @phpstan-ignore-next-line
        $class_c = new class($class_b){function __construct(public readonly \ClassA2|\ClassB2 $test_a){}};
        
        $this->expectException(AutowiringFailureException::class);
        $container->get($class_c::class);
    }

    #[Test]
    public function test_auto_wired_dependency_with_non_type_hinted_dependency_with_default_value_gets_resolved_using_the_default_value(): void {
        $container = new Container();

        $class_a = new class{function __construct(public $test_a = 5){}};
        
        $this->assertInstanceOf($class_a::class, $container->get($class_a::class));
        $this->assertSame(5, $container->get($class_a::class)->test_a);
    }
    
    #[Test]
    public function test_it_throws_AutowiringFailureException_if_the_dependency_is_dependant_on_a_non_type_hinted_parameter_and_no_default_value_is_provided(): void {
        $container = new Container();

        $class_a = new class(0){function __construct(public $test_a){}};
        
        $this->expectException(AutowiringFailureException::class);
        $container->get($class_a::class);
    }

}
