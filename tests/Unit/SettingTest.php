<?php

namespace Spin8\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Spin8\Settings\Enums\SettingsGroups;
use Spin8\Settings\Enums\SettingTypes;
use Spin8\Settings\Setting;
use Spin8\Settings\SettingsSection;
use RuntimeException;
use Spin8\Tests\TestCase;
use TypeError;
use PHPUnit\Framework\Attributes\CoversClass;

use WP_Mock;

#[CoversClass(Setting::class)]
class SettingTest extends TestCase {

    #[Test]
    public function test_setting_object_gets_instantiated(): void {
        $settingSection = $this->createMock(SettingsSection::class);
        $name = $this->faker->slug();
        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);
        $this->assertInstanceOf(Setting::class, Setting::create($settingSection, $this->faker->word(), $name));
    }

    #[Test]
    public function test_setting_title_gets_initialized(): void {
        $settingSection = $this->createMock(SettingsSection::class);        
        $title = $this->faker->word();
        $name = $this->faker->slug();

        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);

        $setting = Setting::create($settingSection, $title, $name);
        $this->assertTrue($title === $setting->title());
    }

    #[Test]
    public function test_setting_name_gets_initialized(): void {
        $settingSection = $this->createMock(SettingsSection::class);        
        $name = $this->faker->slug();

        WP_Mock::userFunction('sanitize_title')->twice()->with($name)->andReturn($name);

        $setting = Setting::create($settingSection, $this->faker->word(), $name);
        $this->assertTrue(config('plugin', 'name') . '-' . slugify($name) === $setting->name());
    }

    #[Test]
    public function test_setting_page_and_section_get_initialized_from_passed_instance_of_settings_groups_enum(): void {
        $name = $this->faker->slug();
        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);

        $setting = Setting::create(SettingsGroups::DISCUSSION, $this->faker->word(), $name);

        $this->assertTrue(SettingsGroups::DISCUSSION->value === $setting->section());
        $this->assertTrue(SettingsGroups::DISCUSSION->value === $setting->page());
    }

    #[Test]
    public function test_setting_page_and_section_get_initialized_from_passed_instance_of_settings_section(): void {
        $section = $this->createMock(SettingsSection::class);

        $name = $this->faker->slug();
        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);        

        $setting = Setting::create($section, $this->faker->word(), $name);
        $this->assertTrue($section->slug() === $setting->section());
        $this->assertTrue($section->page() === $setting->page());
    }

    #[Test]
    public function test_setting_page_and_section_get_initialized_from_passed_string(): void {        
        $section = $this->faker->word();

        $name = $this->faker->slug();
        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);

        $setting = Setting::create($section, $this->faker->word(), $name);
        $this->assertTrue($section === $setting->section());
        $this->assertTrue($section === $setting->page());
    }

    #[Test]
    public function test_setting_object_type_gets_set_in_set_type_method(): void {
        $settingSection = $this->createMock(SettingsSection::class);
        
        $name = $this->faker->slug();
        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);

        $setting = Setting::create($settingSection, $this->faker->word(), $name);
        $setting->setType(SettingTypes::BOOL);
        $this->assertTrue($setting->type() === SettingTypes::BOOL->realValue());
    }

    #[Test]
    public function test_setting_object_sanitize_callback_gets_set_in_set_type_method(): void {
        $settingSection = $this->createMock(SettingsSection::class);
        
        $name = $this->faker->slug();
        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);

        $setting = Setting::create($settingSection, $this->faker->word(), $name);

        $this->assertNull($setting->sanitizeCallback());

        $setting->setType(SettingTypes::BOOL);
        $this->assertSame("sanitize_text_field", $setting->sanitizeCallback());

        $setting->setType(SettingTypes::COLOR);
        $this->assertSame('sanitize_hex_color', $setting->sanitizeCallback());

        $setting->setType(SettingTypes::TEXTAREA);
        $this->assertSame('sanitize_textarea_field', $setting->sanitizeCallback());

        $setting->setType(SettingTypes::ARRAY);
        $this->assertNull($setting->sanitizeCallback());
    }

    #[Test]
    public function test_setting_object_template_gets_set_in_set_type_method(): void {
        $settingSection = $this->createMock(SettingsSection::class);
        
        $name = $this->faker->slug();
        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);

        $setting = Setting::create($settingSection, $this->faker->word(), $name);

        $this->assertNull($setting->template());

        $setting->setType(SettingTypes::BOOL);
        $this->assertSame("partials/checkbox", $setting->template());

        $setting->setType(SettingTypes::COLOR);
        $this->assertSame('partials/input', $setting->template());

        $setting->setType(SettingTypes::TEXTAREA);
        $this->assertSame('partials/textarea', $setting->template());

        $setting->setType(SettingTypes::ARRAY);
        $this->assertNull($setting->template());
    }

    #[Test]
    public function test_setting_object_data_gets_set_in_set_type_method(): void {
        $settingSection = $this->createMock(SettingsSection::class);
        
        $name = $this->faker->slug();
        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);

        $setting = Setting::create($settingSection, $this->faker->word(), $name);

        $this->assertEmpty($setting->data());

        $setting->setType(SettingTypes::BOOL);
        $this->assertEmpty($setting->data());

        $setting->setType(SettingTypes::COLOR);
        $this->assertSame(['type' => 'color'], $setting->data());

        $setting->setType(SettingTypes::TEXTAREA);
        $this->assertEmpty($setting->data());

        $setting->setType(SettingTypes::ARRAY);
        $this->assertEmpty($setting->data());

        $setting->setType(SettingTypes::NUMBER);
        $this->assertSame(['type' => 'number', 'step' => '.01'], $setting->data());
    }

    #[Test]
    public function test_exception_is_thrown_when_trying_to_set_type_if_passed_type_is_not_compatible_with_already_set_default(): void {
        $settingSection = $this->createMock(SettingsSection::class);
        
        $name = $this->faker->slug();
        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);

        $setting = Setting::create($settingSection, $this->faker->word(), $name);

        $this->assertNull($setting->default());
        $this->assertNull($setting->type());

        $setting->setDefault(1);
        $this->assertSame(1, $setting->default());

        $setting->setType(SettingTypes::INT);
        $this->assertTrue($setting->type() === SettingTypes::INT->value);

        $this->expectException(TypeError::class);
        $setting->setType(SettingTypes::STRING);
    }

    #[Test]
    public function test_setting_object_page_gets_set_by_set_page_method(): void {
        $settingSection = $this->createMock(SettingsSection::class);
        
        $name = $this->faker->slug();
        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);

        $setting = Setting::create($settingSection, $this->faker->word(), $name);
        $setting->setPage('test123');
        $this->assertTrue($setting->page() === 'test123');
    }

    #[Test]
    public function test_setting_object_description_gets_set_by_set_description_method(): void {
        $settingSection = $this->createMock(SettingsSection::class);
        
        $name = $this->faker->slug();
        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);

        $setting = Setting::create($settingSection, $this->faker->word(), $name);
        $this->assertNull($setting->description());
        $setting->setDescription('test123');
        $this->assertTrue($setting->description() === 'test123');
    }

    #[Test]
    public function test_setting_object_default_gets_set_by_set_default_method(): void {
        $settingSection = $this->createMock(SettingsSection::class);
        
        $name = $this->faker->slug();
        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);

        $setting = Setting::create($settingSection, $this->faker->word(), $name);
        $this->assertNull($setting->default());
        $setting->setDefault('test123');
        $this->assertTrue($setting->default() === 'test123');
    }

    #[Test]
    public function test_exception_is_thrown_when_trying_to_set_setting_objects_default_incompatible_with_already_set_type(): void {
        $settingSection = $this->createMock(SettingsSection::class);
        
        $name = $this->faker->slug();
        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);

        $setting = Setting::create($settingSection, $this->faker->word(), $name);

        $this->assertNull($setting->default());
        $this->assertNull($setting->type());

        $setting->setType(SettingTypes::STRING);
        $this->assertTrue($setting->type() === SettingTypes::STRING->value);

        $setting->setDefault('test123');
        $this->assertTrue($setting->default() === 'test123');

        $this->expectException(TypeError::class);
        $setting->setDefault(1);
    }

    #[Test]
    public function test_setting_object_show_in_rest_gets_set_by_set_show_in_rest_method(): void {
        $settingSection = $this->createMock(SettingsSection::class);
        
        $name = $this->faker->slug();
        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);

        $setting = Setting::create($settingSection, $this->faker->word(), $name);

        $this->assertNull($setting->showInRest());

        $setting->setShowInRest(true);
        $this->assertTrue($setting->showInRest());

        $setting->setShowInRest(false);
        $this->assertFalse($setting->showInRest());
    }

    #[Test]
    public function test_setting_object_template_gets_set_by_set_template_method(): void {
        $settingSection = $this->createMock(SettingsSection::class);
        
        $name = $this->faker->slug();
        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);

        $setting = Setting::create($settingSection, $this->faker->word(), $name);

        $this->assertNull($setting->template());

        $setting->setTemplate('test123');
        $this->assertTrue($setting->template() === 'test123');
    }

    #[Test]
    public function test_setting_object_class_gets_set_by_set_class_method(): void {
        $settingSection = $this->createMock(SettingsSection::class);
        
        $name = $this->faker->slug();
        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);

        $setting = Setting::create($settingSection, $this->faker->word(), $name);

        $this->assertEmpty($setting->class());

        $setting->setClass('test123');
        $this->assertTrue($setting->class() === 'test123');
    }

    #[Test]
    public function test_setting_object_data_gets_set_by_with_method(): void {
        $settingSection = $this->createMock(SettingsSection::class);
        
        $name = $this->faker->slug();
        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);

        $setting = Setting::create($settingSection, $this->faker->word(), $name);

        $this->assertEmpty($setting->data());

        $setting->with([1, 2, 3, 4, 5]);
        $this->assertNotEmpty($setting->data());
        $this->assertTrue($setting->data() === [1, 2, 3, 4, 5]);
        $this->assertCount(5, $setting->data());
    }

    #[Test]
    public function test_setting_object_gets_registered_by_register_method(): void {
        $settingSection = $this->createMock(SettingsSection::class);
        $title = $this->faker->word();
        $name = $this->faker->slug();
        
        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);
        $setting = Setting::create($settingSection, $title, $name);

        $setting->setDescription('test123');
        $setting->setTemplate('test123');

        //FIXME: fails because of a bug in WP_Mock. Pull request with fix already sent.
        WP_Mock::expectActionAdded('admin_init', WP_Mock\Functions::type(Closure::class));
        
        $setting->register();
    }

    #[Test]
    public function test_exception_is_thrown_when_registering_setting_object_if_no_template_is_defined(): void {
        $settingSection = $this->createMock(SettingsSection::class);
        $title = $this->faker->word();
        $name = $this->faker->slug();

        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);
        $setting = Setting::create($settingSection, $title, $name);
        
        //FIXME: fails because of a bug in WP_Mock. Pull request with fix already sent.
        WP_Mock::expectActionAdded('admin_init', WP_Mock\Functions::type(Closure::class));

        $this->expectException(RuntimeException::class);
        $setting->register();
    }

    #[Test]
    public function test_exception_is_thrown_when_registering_setting_object_if_sanitize_callback_function_does_not_exists(): void {        
        $settingSection = $this->createMock(SettingsSection::class);
        $title = $this->faker->word();
        $name = $this->faker->slug();

        WP_Mock::userFunction('sanitize_title')->once()->with($name)->andReturn($name);

        $setting = Setting::create($settingSection, $title, $name);
        
        //FIXME: fails because of a bug in WP_Mock. Pull request with fix already sent.
        WP_Mock::expectActionAdded('admin_init', WP_Mock\Functions::type(Closure::class));

        $this->expectException(RuntimeException::class);
        $setting->register('function_does_not_exists');
    }
}
