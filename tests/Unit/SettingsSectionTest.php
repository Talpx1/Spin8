<?php

namespace Spin8\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Spin8\Settings\SettingsPage;
use Spin8\Settings\SettingsSection;
use Mockery;
use Spin8\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function Brain\Monkey\Actions\expectAdded;
use function Brain\Monkey\Functions\stubs;

#[CoversClass(SettingsSection::class)]
class SettingsSectionTest extends TestCase {

    #[Test]
    public function test_settings_section_object_gets_instantiated() {
        stubs(["sanitize_title"]);
        $this->assertInstanceOf(SettingsSection::class, SettingsSection::create(
            $this->faker->word,
            $this->faker->slug,
            $this->createMock(SettingsPage::class)
        ));
    }

    #[Test]
    public function test_settings_section_title_gets_initialized() {
        stubs(['sanitize_title']);
        $title = $this->faker->word;
        $settings_section = SettingsSection::create($title, $this->faker->slug, 'test');
        $this->assertTrue($title === $settings_section->title());
    }

    #[Test]
    public function test_settings_section_page_gets_initialized_using_setting_page_object() {
        stubs(['sanitize_title']);
        $page = SettingsPage::create($this->faker->word, $this->faker->slug);
        $settings_section = SettingsSection::create($this->faker->word, $this->faker->slug, $page);
        $this->assertTrue($page->slug() === $settings_section->page());
    }

    #[Test]
    public function test_settings_section_page_gets_initialized_using_passed_string() {
        stubs(['sanitize_title']);
        $page = $this->faker->slug;
        $settings_section = SettingsSection::create($this->faker->word, $this->faker->slug, $page);
        $this->assertTrue($page === $settings_section->page());
    }

    #[Test]
    public function test_settings_section_slug_gets_initialized() {
        stubs(['sanitize_title']);
        $slug = $this->faker->slug;
        $settings_section = SettingsSection::create($this->faker->word, $slug, 'test');
        $this->assertTrue(config('plugin', 'name') . '-' . slugify($slug) === $settings_section->slug());
    }

    #[Test]
    public function test_settings_section_description_gets_set_by_set_description_method() {
        stubs(['sanitize_title']);
        $settings_section = SettingsSection::create($this->faker->word, $this->faker->slug, 'test');
        $settings_section->setDescription("test123");
        $this->assertTrue("test123" === $settings_section->description());
    }

    #[Test]
    public function test_setting_section_gets_registered_by_register_method() {
        stubs(['sanitize_title', '__']);
        $setting_section = SettingsSection::create($this->faker->word, $this->faker->slug, 'general');
        $setting_section->setDescription('test123');
        expectAdded('admin_init')->once()->with(Mockery::type('Closure'));
        $setting_section->register();
    }
}
