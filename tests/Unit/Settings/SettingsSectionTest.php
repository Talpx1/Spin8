<?php

namespace Spin8\Tests\Unit\Settings;

use Closure;
use PHPUnit\Framework\Attributes\Test;
use Spin8\Settings\SettingsPage;
use Spin8\Settings\SettingsSection;
use Spin8\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use WP_Mock;

#[CoversClass(SettingsSection::class)]
class SettingsSectionTest extends TestCase {

    #[Test]
    public function test_settings_section_object_gets_instantiated(): void {
        $slug = $this->faker->slug();        
        WP_Mock::userFunction('sanitize_title')->once()->with($slug)->andReturn($slug);

        $this->assertInstanceOf(SettingsSection::class, SettingsSection::create(
            $this->faker->word(),
            $slug,
            $this->createMock(SettingsPage::class)
        ));
    }

    #[Test]
    public function test_settings_section_title_gets_initialized(): void {
        $slug = $this->faker->slug();        
        $title = $this->faker->word();
        
        WP_Mock::userFunction('sanitize_title')->once()->with($slug)->andReturn($slug);
        
        $settings_section = SettingsSection::create($title, $slug, 'test');
        $this->assertTrue($title === $settings_section->title());
    }

    #[Test]
    public function test_settings_section_page_gets_initialized_using_setting_page_object(): void {
        $slug = $this->faker->slug();        
        $page = $this->createMock(SettingsPage::class);
        
        WP_Mock::userFunction('sanitize_title')->once()->with($slug)->andReturn($slug);
        
        $settings_section = SettingsSection::create($this->faker->word(), $slug, $page);
        $this->assertTrue($page->slug() === $settings_section->page());
    }

    #[Test]
    public function test_settings_section_page_gets_initialized_using_passed_string(): void {
        $slug = $this->faker->slug();        
        $page = $this->faker->slug();

        WP_Mock::userFunction('sanitize_title')->once()->with($slug)->andReturn($slug);
        
        $settings_section = SettingsSection::create($this->faker->word(), $slug, $page);
        $this->assertTrue($page === $settings_section->page());
    }

    #[Test]
    public function test_settings_section_slug_gets_initialized(): void {
        $slug = $this->faker->slug();

        WP_Mock::userFunction('sanitize_title')->twice()->with($slug)->andReturn($slug);

        $settings_section = SettingsSection::create($this->faker->word(), $slug, 'test');
        $this->assertTrue(config('plugin', 'name') . '-' . slugify($slug) === $settings_section->slug());
    }

    #[Test]
    public function test_settings_section_description_gets_set_by_set_description_method(): void {
        $slug = $this->faker->slug();
        
        WP_Mock::userFunction('sanitize_title')->once()->with($slug)->andReturn($slug);

        $settings_section = SettingsSection::create($this->faker->word(), $slug, 'test');
        $settings_section->setDescription("test123");
        $this->assertTrue("test123" === $settings_section->description());
    }

    #[Test]
    public function test_setting_section_gets_registered_by_register_method(): void {
        $slug = $this->faker->slug();

        WP_Mock::userFunction('sanitize_title')->once()->with($slug)->andReturn($slug);

        $setting_section = SettingsSection::create($this->faker->word(), $slug, 'general');
        $setting_section->setDescription('test123');

        //FIXME: fails because of a bug in WP_Mock. Pull request with fix already sent.
        WP_Mock::expectActionAdded('admin_init', WP_Mock\Functions::type(Closure::class));

        $setting_section->register();
    }
}
