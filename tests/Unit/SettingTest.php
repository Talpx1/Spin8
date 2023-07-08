<?php

namespace Spin8\Tests\Unit;

use Spin8\Settings\Enums\SettingsGroups;
use Spin8\Settings\Enums\SettingTypes;
use Spin8\Settings\Setting;
use Spin8\Settings\SettingsPage;
use Spin8\Settings\SettingsSection;
use Mockery;
use RuntimeException;
use Spin8\Tests\TestCase;
use TypeError;

use function Brain\Monkey\Actions\expectAdded;
use function Brain\Monkey\Functions\stubs;

/**
 * @coversDefaultClass \Spin8\Settings\Setting
 */
class SettingTest extends TestCase {

    /** 
     * @test  
     * @covers ::create
     */
    public function test_setting_object_gets_instantiated() {
        $settingSection = $this->createMock(SettingsSection::class);
        stubs(['sanitize_title']);
        $this->assertInstanceOf(Setting::class, Setting::create($settingSection, self::$faker->word, self::$faker->slug));
    }

    /** 
     * @test  
     * @covers ::__construct
     * @covers ::title
     */
    public function test_setting_title_gets_initialized() {
        $settingSection = $this->createMock(SettingsSection::class);
        stubs(['sanitize_title']);
        $title = self::$faker->word;
        $setting = Setting::create($settingSection, $title, self::$faker->slug);
        $this->assertTrue($title === $setting->title());
    }

    /** 
     * @test  
     * @covers ::__construct
     * @covers ::name
     */
    public function test_setting_name_gets_initialized() {
        $settingSection = $this->createMock(SettingsSection::class);
        stubs(['sanitize_title']);
        $name = self::$faker->slug;
        $setting = Setting::create($settingSection, self::$faker->word, $name);
        $this->assertTrue(config('plugin', 'name') . '-' . slugify($name) === $setting->name());
    }

    /** 
     * @test  
     * @covers ::__construct
     * @covers ::section
     * @covers ::page
     */
    public function test_setting_page_and_section_get_initialized_from_passed_instance_of_settings_groups_enum() {
        stubs(['sanitize_title']);
        $setting = Setting::create(SettingsGroups::DISCUSSION, self::$faker->word, self::$faker->slug);
        $this->assertTrue(SettingsGroups::DISCUSSION->value === $setting->section());
        $this->assertTrue(SettingsGroups::DISCUSSION->value === $setting->page());
    }

    /** 
     * @test  
     * @covers ::__construct
     * @covers ::section
     * @covers ::page
     */
    public function test_setting_page_and_section_get_initialized_from_passed_instance_of_settings_section() {
        stubs(['sanitize_title']);
        $section = SettingsSection::create(self::$faker->word, self::$faker->slug, $this->createMock(SettingsPage::class));
        $setting = Setting::create($section, self::$faker->word, self::$faker->slug);
        $this->assertTrue($section->slug() === $setting->section());
        $this->assertTrue($section->page() === $setting->page());
    }

    /** 
     * @test  
     * @covers ::__construct
     * @covers ::section
     * @covers ::page
     */
    public function test_setting_page_and_section_get_initialized_from_passed_string() {
        stubs(['sanitize_title']);
        $section = self::$faker->word;
        $setting = Setting::create($section, self::$faker->word, self::$faker->slug);
        $this->assertTrue($section === $setting->section());
        $this->assertTrue($section === $setting->page());
    }

    /** 
     * @test  
     * @covers ::setType
     * @covers ::type
     */
    public function test_setting_object_type_gets_set_in_set_type_method() {
        $settingSection = $this->createMock(SettingsSection::class);
        stubs(['sanitize_title']);
        $setting = Setting::create($settingSection, self::$faker->word, slugify(self::$faker->word));
        $setting->setType(SettingTypes::BOOL);
        $this->assertTrue($setting->type() === SettingTypes::BOOL->realValue());
    }

    /** 
     * @test  
     * @covers ::setType
     * @covers ::sanitizeCallback
     */
    public function test_setting_object_sanitize_callback_gets_set_in_set_type_method() {
        $settingSection = $this->createMock(SettingsSection::class);
        stubs(['sanitize_title']);
        $setting = Setting::create($settingSection, self::$faker->word, slugify(self::$faker->word));

        $this->assertNull($setting->sanitizeCallback());

        $setting->setType(SettingTypes::BOOL);
        $this->assertTrue($setting->sanitizeCallback() === "sanitize_text_field");

        $setting->setType(SettingTypes::COLOR);
        $this->assertTrue($setting->sanitizeCallback() === 'sanitize_hex_color');

        $setting->setType(SettingTypes::TEXTAREA);
        $this->assertTrue($setting->sanitizeCallback() === 'sanitize_textarea_field');

        $setting->setType(SettingTypes::ARRAY);
        $this->assertNull($setting->sanitizeCallback());
    }

    /** 
     * @test  
     * @covers ::setType
     * @covers ::template
     */
    public function test_setting_object_template_gets_set_in_set_type_method() {
        $settingSection = $this->createMock(SettingsSection::class);
        stubs(['sanitize_title']);
        $setting = Setting::create($settingSection, self::$faker->word, slugify(self::$faker->word));

        $this->assertNull($setting->template());

        $setting->setType(SettingTypes::BOOL);
        $this->assertTrue($setting->template() === "partials/checkbox");

        $setting->setType(SettingTypes::COLOR);
        $this->assertTrue($setting->template() === 'partials/input');

        $setting->setType(SettingTypes::TEXTAREA);
        $this->assertTrue($setting->template() === 'partials/textarea');

        $setting->setType(SettingTypes::ARRAY);
        $this->assertNull($setting->template());
    }

    /** 
     * @test  
     * @covers ::setType
     * @covers ::data
     */
    public function test_setting_object_data_gets_set_in_set_type_method() {
        $settingSection = $this->createMock(SettingsSection::class);
        stubs(['sanitize_title']);
        $setting = Setting::create($settingSection, self::$faker->word, slugify(self::$faker->word));

        $this->assertEmpty($setting->data());

        $setting->setType(SettingTypes::BOOL);
        $this->assertEmpty($setting->data());

        $setting->setType(SettingTypes::COLOR);
        $this->assertTrue($setting->data() === ['type' => 'color']);

        $setting->setType(SettingTypes::TEXTAREA);
        $this->assertEmpty($setting->data());

        $setting->setType(SettingTypes::ARRAY);
        $this->assertEmpty($setting->data());

        $setting->setType(SettingTypes::NUMBER);
        $this->assertTrue($setting->data() === ['type' => 'number', 'step' => '.01']);
    }

    /** 
     * @test  
     * @covers ::setType
     */
    public function test_exception_is_thrown_when_trying_to_set_type_if_passed_type_is_not_compatible_with_already_set_default() {
        $settingSection = $this->createMock(SettingsSection::class);
        stubs(['sanitize_title', '__']);
        $setting = Setting::create($settingSection, self::$faker->word, slugify(self::$faker->word));

        $this->assertNull($setting->default());
        $this->assertNull($setting->type());

        $setting->setDefault(1);
        $this->assertTrue($setting->default() === 1);

        $setting->setType(SettingTypes::INT);
        $this->assertTrue($setting->type() === SettingTypes::INT->value);

        $this->expectException(TypeError::class);
        $setting->setType(SettingTypes::STRING);
    }

    /** 
     * @test  
     * @covers ::setPage
     * @covers ::page
     */
    public function test_setting_object_page_gets_set_by_set_page_method() {
        $settingSection = $this->createMock(SettingsSection::class);
        stubs(['sanitize_title']);
        $setting = Setting::create($settingSection, self::$faker->word, slugify(self::$faker->word));
        $setting->setPage('test123');
        $this->assertTrue($setting->page() === 'test123');
    }

    /** 
     * @test  
     * @covers ::setDescription
     * @covers ::description
     */
    public function test_setting_object_description_gets_set_by_set_description_method() {
        $settingSection = $this->createMock(SettingsSection::class);
        stubs(['sanitize_title']);
        $setting = Setting::create($settingSection, self::$faker->word, slugify(self::$faker->word));
        $this->assertNull($setting->description());
        $setting->setDescription('test123');
        $this->assertTrue($setting->description() === 'test123');
    }

    /** 
     * @test  
     * @covers ::setDefault
     * @covers ::default
     */
    public function test_setting_object_default_gets_set_by_set_default_method() {
        $settingSection = $this->createMock(SettingsSection::class);
        stubs(['sanitize_title']);
        $setting = Setting::create($settingSection, self::$faker->word, slugify(self::$faker->word));
        $this->assertNull($setting->default());
        $setting->setDefault('test123');
        $this->assertTrue($setting->default() === 'test123');
    }

    /** 
     * @test  
     * @covers ::setDefault
     * @covers ::default
     */
    public function test_exception_is_thrown_when_trying_to_set_setting_objects_default_incompatible_with_already_set_type() {
        $settingSection = $this->createMock(SettingsSection::class);
        stubs(['sanitize_title', '__']);
        $setting = Setting::create($settingSection, self::$faker->word, slugify(self::$faker->word));

        $this->assertNull($setting->default());
        $this->assertNull($setting->type());

        $setting->setType(SettingTypes::STRING);
        $this->assertTrue($setting->type() === SettingTypes::STRING->value);

        $setting->setDefault('test123');
        $this->assertTrue($setting->default() === 'test123');

        $this->expectException(TypeError::class);
        $setting->setDefault(1);
    }

    /** 
     * @test  
     * @covers ::setShowInRest
     * @covers ::showInRest
     */
    public function test_setting_object_show_in_rest_gets_set_by_set_show_in_rest_method() {
        $settingSection = $this->createMock(SettingsSection::class);
        stubs(['sanitize_title']);
        $setting = Setting::create($settingSection, self::$faker->word, slugify(self::$faker->word));

        $this->assertNull($setting->showInRest());

        $setting->setShowInRest(true);
        $this->assertTrue($setting->showInRest());

        $setting->setShowInRest(false);
        $this->assertFalse($setting->showInRest());
    }

    /** 
     * @test  
     * @covers ::setTemplate
     * @covers ::template
     */
    public function test_setting_object_template_gets_set_by_set_template_method() {
        $settingSection = $this->createMock(SettingsSection::class);
        stubs(['sanitize_title']);
        $setting = Setting::create($settingSection, self::$faker->word, slugify(self::$faker->word));

        $this->assertNull($setting->template());

        $setting->setTemplate('test123');
        $this->assertTrue($setting->template() === 'test123');
    }

    /** 
     * @test  
     * @covers ::setClass
     * @covers ::class
     */
    public function test_setting_object_class_gets_set_by_set_class_method() {
        $settingSection = $this->createMock(SettingsSection::class);
        stubs(['sanitize_title']);
        $setting = Setting::create($settingSection, self::$faker->word, slugify(self::$faker->word));

        $this->assertEmpty($setting->class());

        $setting->setClass('test123');
        $this->assertTrue($setting->class() === 'test123');
    }

    /** 
     * @test  
     * @covers ::with
     * @covers ::data
     */
    public function test_setting_object_data_gets_set_by_with_method() {
        $settingSection = $this->createMock(SettingsSection::class);
        stubs(['sanitize_title']);
        $setting = Setting::create($settingSection, self::$faker->word, slugify(self::$faker->word));

        $this->assertEmpty($setting->data());

        $setting->with([1, 2, 3, 4, 5]);
        $this->assertNotEmpty($setting->data());
        $this->assertTrue($setting->data() === [1, 2, 3, 4, 5]);
        $this->assertCount(5, $setting->data());
    }

    /** 
     * @test  
     * @covers ::register
     */
    public function test_setting_object_gets_registered_by_register_method() {
        stubs(['sanitize_title', '__']);
        $settingSection = SettingsSection::create(self::$faker->word, self::$faker->slug, 'general');
        $title = self::$faker->word;
        $name = self::$faker->slug;
        $setting = Setting::create($settingSection, $title, $name);

        $setting->setDescription('test123');
        $setting->setTemplate('test123');
        expectAdded('admin_init')->times(2)->with(Mockery::type('Closure'));
        $setting->register();
    }

    /** 
     * @test  
     * @covers ::register
     */
    public function test_exception_is_thrown_when_registering_setting_object_if_no_template_is_defined() {
        stubs(['sanitize_title', '__']);
        $settingSection = SettingsSection::create(self::$faker->word, self::$faker->slug, 'general');
        $title = self::$faker->word;
        $name = self::$faker->slug;

        $setting = Setting::create($settingSection, $title, $name);
        expectAdded('admin_init')->once()->with(Mockery::type('Closure'));
        $this->expectException(RuntimeException::class);
        $setting->register();
    }

    /** 
     * @test  
     * @covers ::register
     */
    public function test_exception_is_thrown_when_registering_setting_object_if_sanitize_callback_function_does_not_exists() {
        stubs(['sanitize_title', '__']);
        $settingSection = SettingsSection::create(self::$faker->word, self::$faker->slug, 'general');
        $title = self::$faker->word;
        $name = self::$faker->slug;

        $setting = Setting::create($settingSection, $title, $name);
        expectAdded('admin_init')->never();
        $this->expectException(RuntimeException::class);
        $setting->register('function_does_not_exists');
    }
}
