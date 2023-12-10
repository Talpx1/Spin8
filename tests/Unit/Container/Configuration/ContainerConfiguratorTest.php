<?php

namespace Spin8\Tests\Unit\Container\Configuration;

use Exception;
use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Spin8\Container\Configuration\AbstractContainerConfigurator;
use Spin8\Container\Configuration\ContainerConfigurator;
use Spin8\Container\Container;
use Spin8\Container\Exceptions\ConfigurationException;
use Spin8\TemplatingEngine\Engines\LatteEngine;
use Spin8\TemplatingEngine\TemplatingEngine;

#[CoversClass(ContainerConfigurator::class)]
#[CoversClass(AbstractContainerConfigurator::class)]
final class ContainerConfiguratorTest extends \PHPUnit\Framework\TestCase {

    protected MockObject $container;

    public function setUp(): void {
        parent::setUp();
        
        $this->container = $this->createMock(Container::class);
    }

    public function tearDown(): void {
        unset($this->container);
        
        parent::tearDown();
    }





    #[Test]
    public function test_can_be_constructed_with_configuration_array(): void {        
        // @phpstan-ignore-next-line
        $configurator = new ContainerConfigurator(["aliases" => []]);

        $this->assertInstanceOf(ContainerConfigurator::class, $configurator);
    }

    #[Test]
    public function test_can_be_constructed_with_configuration_file_path(): void {       
        $root = vfsStream::setup(); 
        $file = vfsStream::newFile("container_config.php")->at($root)->setContent("<?php return ['aliases' => []];");

        $configurator = new ContainerConfigurator($file->url());

        $this->assertInstanceOf(ContainerConfigurator::class, $configurator);
    }

    // @phpstan-ignore-next-line
    public static function container_empty_configurations_provider(): array{
        return [
            [[]],
            [""],
        ];
    }

    /** @param array<string, mixed> $configurations */
    #[Test]
    #[DataProvider('container_empty_configurations_provider')]
    public function test_it_throws_InvalidArgumentException_if_passed_configurations_are_empty(array|string $configurations): void {        
        $this->expectException(InvalidArgumentException::class);

        // @phpstan-ignore-next-line
        new ContainerConfigurator($configurations);
    }

    #[Test]
    public function test_if_configuration_file_path_is_passed_it_throws_ConfigurationException_if_file_does_not_exists(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Could not find configuration file in test.php");

        new ContainerConfigurator("test.php");
    }

    #[Test]
    public function test_if_configuration_file_path_is_passed_it_throws_ConfigurationException_if_file_is_not_readable(): void {        
        $root = vfsStream::setup(); 
        $file = vfsStream::newFile("test.php", 111)->at($root);

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Could not read configuration from file {$file->url()}");

        new ContainerConfigurator($file->url());
    }

    #[Test]
    public function test_configure_method_calls_run_method(): void {        
        $configurator = $this->createPartialMock(ContainerConfigurator::class, ['run']);

        $configurator->expects($this->once())->method('run');

        // @phpstan-ignore-next-line
        $configurator->configure($this->container);
    }

    #[Test]
    public function test_run_method_calls_configureAliases_method(): void {        
        $configurator = $this->getMockBuilder(ContainerConfigurator::class)
            ->setConstructorArgs(["configurations" => ["aliases"=>[]]])
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->onlyMethods(['configureAliases'])
            ->getMock();

        $configurator->expects($this->once())->method('configureAliases');
        
        // @phpstan-ignore-next-line
        $configurator->configure($this->container);
    }

    #[Test]
    public function test_run_method_calls_configureTemplatingEngines_method(): void {        
        $configurator = $this->getMockBuilder(ContainerConfigurator::class)
            ->setConstructorArgs(["configurations" => ["aliases"=>[]]])
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->onlyMethods(['configureTemplatingEngines'])
            ->getMock();

        $configurator->expects($this->once())->method('configureTemplatingEngines');

        // @phpstan-ignore-next-line
        $configurator->configure($this->container);
    }

    #[Test]
    public function test_run_method_calls_configureSingletons_method(): void {        
        $configurator = $this->getMockBuilder(ContainerConfigurator::class)
            ->setConstructorArgs(["configurations" => ["aliases"=>[]]])
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->onlyMethods(['configureSingletons'])
            ->getMock();

        $configurator->expects($this->once())->method('configureSingletons');

        // @phpstan-ignore-next-line
        $configurator->configure($this->container);
    }

    #[Test]
    public function test_run_method_calls_configureEntries_method(): void {        
        $configurator = $this->getMockBuilder(ContainerConfigurator::class)
            ->setConstructorArgs(["configurations" => ["aliases"=>[]]])
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->onlyMethods(['configureEntries'])
            ->getMock();

        $configurator->expects($this->once())->method('configureEntries');

        // @phpstan-ignore-next-line
        $configurator->configure($this->container);
    }


    #[Test]
    public function test_if_aliases_key_is_not_array_in_configurations_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('The key aliases of a container configuration must be an array. string passed.');

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["aliases"=>"test"]))->configure($this->container);
    }

    #[Test]
    public function test_if_alias_key_is_not_string_in_aliases_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('An alias binding key must be a string.');

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["aliases"=>[123=>"test"]]))->configure($this->container);
    }

    #[Test]
    public function test_if_alias_key_is_empty_in_aliases_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('An alias binding key must be a non-empty string. Empty string passed (empty-like values are considered empty).');

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["aliases"=>[""=>"test"]]))->configure($this->container);
    }

    #[Test]
    public function test_if_alias_binding_is_not_string_in_aliases_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('An alias binding value must be a  string (class string).');

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["aliases"=>["test"=>123]]))->configure($this->container);
    }

    #[Test]
    public function test_if_alias_binding_is_empty_in_aliases_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('An alias binding value must be a non-empty class string. Empty string passed (empty-like values are considered empty).');

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["aliases"=>["test"=>""]]))->configure($this->container);
    }

    #[Test]
    public function test_if_alias_binding_is_not_a_valid_class_string_in_aliases_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("'test' does not reference a valid class.");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["aliases"=>["test"=>"test"]]))->configure($this->container);
    }

    #[Test]
    public function test_it_calls_alias_method_in_container_with_alias_and_binding_from_alias_configs(): void {        
        $this->container->expects($this->once())->method("alias")->with("test", \ArrayObject::class);

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["aliases"=>["test"=>\ArrayObject::class]]))->configure($this->container);

    }

    #[Test]
    public function test_if_templating_engines_key_is_not_array_in_configurations_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('The key templating_engines of a container configuration must be an array. string passed.');

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["templating_engines"=>"test"]))->configure($this->container);
    }

    #[Test]
    public function test_if_alias_key_is_not_string_in_templating_engines_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("A templating engine binding key must be a string in a container configuration. integer passed.");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["templating_engines"=>[123=>"test"]]))->configure($this->container);
    }

    #[Test]
    public function test_if_alias_key_is_empty_in_templating_engines_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("A templating engine binding key must be a non-empty string in a container configuration. Empty string passed (empty-like values are considered empty).");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["templating_engines"=>[""=>"test"]]))->configure($this->container);
    }

    #[Test]
    public function test_if_alias_binding_is_not_string_in_templating_engines_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("A templating engine binding value must be a string (class-string) in a container configuration. integer passed.");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["templating_engines"=>["test"=>123]]))->configure($this->container);
    }

    #[Test]
    public function test_if_alias_binding_is_empty_in_templating_engines_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("A templating engine binding value must be a non-empty string (class-string) in a container configuration. Empty string passed (empty-like values are considered empty).");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["templating_engines"=>["test"=>""]]))->configure($this->container);
    }

    #[Test]
    public function test_if_alias_binding_is_not_a_valid_class_string_in_templating_engines_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("'test' does not reference a valid class.");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["templating_engines"=>["test"=>"test"]]))->configure($this->container);
    }

    #[Test]
    public function test_if_alias_binding_is_not_a_subclass_of_TemplatingEngine_in_templating_engines_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("A templating engine binding value must be a valid reference to a class that extends ".TemplatingEngine::class.".");

        $class = new class{};
        
        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["templating_engines"=>["test"=>$class::class]]))->configure($this->container);
    }

    #[Test]
    public function test_it_calls_singleton_method_in_container_with_binding_from_templating_engine_configs(): void {                
        $engine = $this->createMock(LatteEngine::class);

        $class = new class("test", "test", $engine) extends TemplatingEngine{
            public function render(string $path, array $data = []): void {}
            public function setTempPath(string $path): void {}
        };

        $this->container->expects($this->once())->method("singleton")->with($class::class);

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["templating_engines"=>["test"=>$class::class]]))->configure($this->container);
    }

    #[Test]
    public function test_it_calls_alias_method_in_container_with_alias_and_binding_from_templating_engine_configs(): void {        
        $engine = $this->createMock(LatteEngine::class);

        $class = new class("test", "test", $engine) extends TemplatingEngine{
            public function render(string $path, array $data = []): void {}
            public function setTempPath(string $path): void {}
        };

        $this->container->expects($this->once())->method("alias")->with("test", $class::class);

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["templating_engines"=>["test"=>$class::class]]))->configure($this->container);
    }

    #[Test]
    public function test_if_singletons_key_is_not_array_in_configurations_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('The key singletons of a container configuration must be an array. string passed.');

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["singletons"=>"test"]))->configure($this->container);
    }

    #[Test]
    public function test_if_binding_key_is_empty_string_in_singletons_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("A singleton binding key must be a non-empty string in a container configuration. Empty string passed (empty-like values are considered empty).");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["singletons"=>[""=>"test"]]))->configure($this->container);
    }

    #[Test]
    public function test_if_binding_key_is_invalid_class_string_in_singletons_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("'test' does not reference a valid class.");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["singletons"=>["test"=>"test"]]))->configure($this->container);
    }

    #[Test]
    public function test_if_binding_key_is_valid_class_string_and_binding_value_is_not_an_object_in_singletons_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("A singleton binding value must be an object when using <class string => binding> in a container configuration. string passed.");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["singletons"=>[\ArrayObject::class=>"test"]]))->configure($this->container);
    }

    #[Test]
    public function test_if_binding_key_is_valid_class_string_and_binding_value_is_not_an_instance_of_the_binding_value_in_singletons_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("A singleton binding value an instance of the class string key when using <class string => binding> in a container configuration. ".\ArrayObject::class." => ".\Exception::class." passed.");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["singletons"=>[\ArrayObject::class=>new \Exception()]]))->configure($this->container);
    }

    #[Test]
    public function test_if_binding_key_is_valid_class_string_and_binding_value_is_an_instance_of_the_binding_value_it_calls_singleton_method_on_container_with_binding_from_singletons_configs(): void {        
        $value = new \LogicException();
        
        $this->container->expects($this->once())->method("singleton")->with(Exception::class, $value);

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["singletons"=>[\Exception::class=>$value]]))->configure($this->container);
    }

    #[Test]
    public function test_if_binding_key_is_int_and_binding_value_is_not_string_in_singletons_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("A singleton binding value must be a string (class string) when using <no key => binding> in a container configuration. integer passed.");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["singletons"=>[0=>1]]))->configure($this->container);
    }

    #[Test]
    public function test_if_binding_key_is_int_and_binding_value_is_an_empty_string_in_singletons_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("A singleton binding value must be a non-empty string in a container configuration. Empty string passed (empty-like values are considered empty).");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["singletons"=>[0=>""]]))->configure($this->container);
    }

    #[Test]
    public function test_if_binding_key_is_int_and_binding_value_is_an_invalid_class_string_in_singletons_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("'test' does not reference a valid class.");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["singletons"=>[0=>"test"]]))->configure($this->container);
    }

    #[Test]
    public function test_if_binding_key_is_int_and_binding_value_is_a_valid_class_string_it_calls_singleton_method_on_container_with_binding_from_singletons_configs(): void {        
        $this->container->expects($this->once())->method("singleton")->with(\ArrayObject::class);

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["singletons"=>[0=>\ArrayObject::class]]))->configure($this->container);
    }

    #[Test]
    public function test_if_entries_key_is_not_array_in_configurations_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('The key entries of a container configuration must be an array. string passed.');

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["entries"=>"test"]))->configure($this->container);
    }

    #[Test]
    public function test_if_binding_key_is_empty_string_in_entries_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("An entry binding key must be a non-empty string in a container configuration. Empty string passed (empty-like values are considered empty).");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["entries"=>[""=>"test"]]))->configure($this->container);
    }

    #[Test]
    public function test_if_binding_key_is_invalid_class_string_in_entries_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("'test' does not reference a valid class.");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["entries"=>["test"=>"test"]]))->configure($this->container);
    }

    #[Test]
    public function test_if_binding_key_is_valid_class_string_and_binding_value_is_not_a_string_in_entries_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("An entry binding value must be a class string when using <class string => class string> bindings in a container configuration. integer passed.");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["entries"=>[\ArrayObject::class=>1]]))->configure($this->container);
    }

    #[Test]
    public function test_if_binding_key_is_valid_class_string_and_binding_value_is_not_an_instantiable_class_string_in_entries_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("An entry binding value must be an instantiable class string when using <class string => class string> bindings in a container configuration. Non instantiable class string passed.");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["entries"=>[\ArrayObject::class=>\Throwable::class]]))->configure($this->container);
    }

    #[Test]
    public function test_if_binding_key_is_valid_class_string_and_binding_value_is_an_instantiable_class_string_it_calls_bind_method_on_container_with_binding_from_entries_configs(): void {        
        $this->container->expects($this->once())->method("bind")->with(Exception::class, \LogicException::class);

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["entries"=>[Exception::class=>\LogicException::class]]))->configure($this->container);
    }

    #[Test]
    public function test_if_binding_key_is_int_and_binding_value_is_not_string_in_entries_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("An entry binding value must be a string (class string) when using <no key => binding> in a container configuration. integer passed.");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["entries"=>[0=>1]]))->configure($this->container);
    }

    #[Test]
    public function test_if_binding_key_is_int_and_binding_value_is_an_empty_string_in_entries_configuration_it_throws_ConfigurationException(): void {        
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("An entry binding value must be a non-empty string in a container configuration. Empty string passed (empty-like values are considered empty).");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["entries"=>[0=>""]]))->configure($this->container);
    }

    #[Test]
    public function test_if_binding_key_is_int_and_binding_value_is_not_an_instantiable_class_string_in_entries_configuration_it_throws_ConfigurationException(): void {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("An entry binding value must be an instantiable class string when using <no class => class string> bindings in a container configuration. Non instantiable class string passed. If you are trying to bind an interface or some other non instantiable class, use <class string => class string>");

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["entries"=>[0=>\ReflectionType::class]]))->configure($this->container);
    }

    #[Test]
    public function test_if_binding_key_is_int_and_binding_value_is_an_instantiable_class_string_it_calls_bind_method_on_container_with_binding_from_entries_configs(): void {        
        $this->container->expects($this->once())->method("bind")->with(\ArrayObject::class);

        // @phpstan-ignore-next-line
        (new ContainerConfigurator(["entries"=>[0=>\ArrayObject::class]]))->configure($this->container);
    }

    // @phpstan-ignore-next-line
    public static function config_singleton_dependency_provider(): array{
        return [
            [\ArrayObject::class],
            [[Exception::class => new \LogicException()]],
        ];
    }

    /** @param string|array{int|class-string, class-string|object} $configurations */
    #[Test]
    #[DataProvider('config_singleton_dependency_provider')]
    public function test_tries_to_resolve_a_config_singleton_dependency_from_the_loaded_configs_and_register_binding_in_container(array|string $configurations): void {
        // @phpstan-ignore-next-line
        $configurator = new ContainerConfigurator(["singletons"=>is_array($configurations) ? $configurations : [$configurations]]);

        // @phpstan-ignore-next-line
        $configurator->configure($this->container);

        $params = is_array($configurations) ? [array_key_first($configurations), $configurations[array_key_first($configurations)]] : [$configurations];

        $this->container->expects($this->once())->method("singleton")->with(...$params);

        $id = is_array($configurations) ? array_key_first($configurations) : $configurations;
        
        // @phpstan-ignore-next-line
        $configurator->resolveDependencyFromConfigs($id);
    }

    // @phpstan-ignore-next-line
    public static function config_entry_dependency_provider(): array{
        return [
            [\ArrayObject::class],
            [[Exception::class => \LogicException::class]],
        ];
    }

    /** @param string|array{int|class-string, class-string|object} $configurations */
    #[Test]
    #[DataProvider('config_entry_dependency_provider')]
    public function test_tries_to_resolve_a_config_entry_dependency_from_the_loaded_configs_and_register_binding_in_container(array|string $configurations): void {
        // @phpstan-ignore-next-line
        $configurator = new ContainerConfigurator(["entries"=>is_array($configurations) ? $configurations : [$configurations]]);

        // @phpstan-ignore-next-line
        $configurator->configure($this->container);

        $params = is_array($configurations) ? [array_key_first($configurations), $configurations[array_key_first($configurations)]] : [$configurations];

        $this->container->expects($this->once())->method("bind")->with(...$params);

        $id = is_array($configurations) ? array_key_first($configurations) : $configurations;
        
        // @phpstan-ignore-next-line
        $configurator->resolveDependencyFromConfigs($id);
    }


    #[Test]
    public function test_it_returns_false_if_it_can_not_resolve_a_config_dependency_from_the_loaded_configs(): void {
        // @phpstan-ignore-next-line
        $this->assertFalse((new ContainerConfigurator(["singletons"=>[]]))->resolveDependencyFromConfigs(\ArrayObject::class));
    }

}
