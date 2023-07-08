<?php

namespace Spin8\Tests\Unit;

use Spin8\Settings\SettingsPage;
use Spin8\Settings\SettingsSection;
use Mockery;
use Spin8\Tests\TestCase;

use function Brain\Monkey\Actions\expectAdded;
use function Brain\Monkey\Functions\stubs;

/**
 * @coversDefaultClass \Spin8\Settings\SettingsSection
 */
class SettingsSectionTest extends TestCase {

    /** 
     * @test  
     * @covers ::create
     */
    public function test_settings_section_object_gets_instantiated() {
        stubs(["sanitize_title"]);
        $this->assertInstanceOf(SettingsSection::class, SettingsSection::create(
            self::$faker->word,
            self::$faker->slug,
            $this->createMock(SettingsPage::class)
        ));
    }

    /** 
     * @test  
     * @covers ::__construct
     * @covers ::title
     */
    public function test_settings_section_title_gets_initialized() {
        stubs(['sanitize_title']);
        $title = self::$faker->word;
        $settings_section = SettingsSection::create($title, self::$faker->slug, 'test');
        $this->assertTrue($title === $settings_section->title());
    }

    /** 
     * @test  
     * @covers ::__construct
     * @covers ::page
     */
    public function test_settings_section_page_gets_initialized_using_setting_page_object() {
        stubs(['sanitize_title']);
        $page = SettingsPage::create(self::$faker->word, self::$faker->slug);
        $settings_section = SettingsSection::create(self::$faker->word, self::$faker->slug, $page);
        $this->assertTrue($page->slug() === $settings_section->page());
    }

    /** 
     * @test  
     * @covers ::__construct
     * @covers ::page
     */
    public function test_settings_section_page_gets_initialized_using_passed_string() {
        stubs(['sanitize_title']);
        $page = self::$faker->slug;
        $settings_section = SettingsSection::create(self::$faker->word, self::$faker->slug, $page);
        $this->assertTrue($page === $settings_section->page());
    }

    /** 
     * @test  
     * @covers ::__construct
     * @covers ::slug
     */
    public function test_settings_section_slug_gets_initialized() {
        stubs(['sanitize_title']);
        $slug = self::$faker->slug;
        $settings_section = SettingsSection::create(self::$faker->word, $slug, 'test');
        $this->assertTrue(config('plugin', 'name') . '-' . slugify($slug) === $settings_section->slug());
    }

    /** 
     * @test  
     * @covers ::setDescription
     * @covers ::description
     */
    public function test_settings_section_description_gets_set_by_set_description_method() {
        stubs(['sanitize_title']);
        $settings_section = SettingsSection::create(self::$faker->word, self::$faker->slug, 'test');
        $settings_section->setDescription("test123");
        $this->assertTrue("test123" === $settings_section->description());
    }

    /** 
     * @test  
     * @covers ::register
     */
    public function test_setting_section_gets_registered_by_register_method() {
        stubs(['sanitize_title', '__']);
        $setting_section = SettingsSection::create(self::$faker->word, self::$faker->slug, 'general');
        $setting_section->setDescription('test123');
        expectAdded('admin_init')->once()->with(Mockery::type('Closure'));
        $setting_section->register();
    }
}
