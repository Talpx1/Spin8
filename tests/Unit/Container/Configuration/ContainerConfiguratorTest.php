<?php

namespace Spin8\Tests\Unit\Container\Configuration;

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

//TODO
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
        
    }

    #[Test]
    public function test_if_alias_key_is_not_string_in_aliases_configuration_it_throws_ConfigurationException(): void {        
        
    }

    #[Test]
    public function test_if_alias_key_is_empty_in_aliases_configuration_it_throws_ConfigurationException(): void {        
        
    }

    #[Test]
    public function test_if_alias_binding_is_not_string_in_aliases_configuration_it_throws_ConfigurationException(): void {        
        
    }

    #[Test]
    public function test_if_alias_binding_is_empty_in_aliases_configuration_it_throws_ConfigurationException(): void {        
        
    }

    #[Test]
    public function test_if_alias_binding_is_not_a_valid_class_string_in_aliases_configuration_it_throws_ConfigurationException(): void {        
        
    }

    #[Test]
    public function test_it_calls_alias_method_in_container_with_alias_and_binding_from_alias_configs(): void {        
        
    }

    #[Test]
    public function test_if_templating_engines_key_is_not_array_in_configurations_it_throws_ConfigurationException(): void {        
        
    }

    #[Test]
    public function test_if_alias_key_is_not_string_in_templating_engines_configuration_it_throws_ConfigurationException(): void {        
        
    }

    #[Test]
    public function test_if_alias_key_is_empty_in_templating_engines_configuration_it_throws_ConfigurationException(): void {        
        
    }

    #[Test]
    public function test_if_alias_binding_is_not_string_in_templating_engines_configuration_it_throws_ConfigurationException(): void {        
        
    }

    #[Test]
    public function test_if_alias_binding_is_empty_in_templating_engines_configuration_it_throws_ConfigurationException(): void {        
        
    }

    #[Test]
    public function test_if_alias_binding_is_not_a_valid_class_string_in_templating_engines_configuration_it_throws_ConfigurationException(): void {        
        
    }

    #[Test]
    public function test_if_alias_binding_is_not_a_subclass_of_TemplatingEngine_in_templating_engines_configuration_it_throws_ConfigurationException(): void {        
        
    }

    #[Test]
    public function test_it_calls_singleton_method_in_container_with_binding_from_templating_engine_configs(): void {        

    }

    #[Test]
    public function test_it_calls_alias_method_in_container_with_alias_and_binding_from_templating_engine_configs(): void {        
        
    }
}
