<?php

namespace Spin8\Tests\Unit\WP\Settings;

use Closure;
use PHPUnit\Framework\Attributes\Test;
use Spin8\WP\MenuPage;
use Spin8\WP\Settings\SettingsPage;
use Spin8\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use WP_Mock;

#[CoversClass(SettingsPage::class)]
class SettingsPageTest extends TestCase {

    #[Test]
    public function test_settings_page_object_gets_instantiated(): void {
        $menu_title = $this->faker->word();
        WP_Mock::userFunction('remove_accents')->once()->with($menu_title)->andReturn($menu_title);
        WP_Mock::userFunction('sanitize_title_with_dashes')->once()->with($menu_title, '', 'save')->andReturn($menu_title);

        $this->assertInstanceOf(SettingsPage::class, SettingsPage::create($menu_title, $this->faker->slug()));
    }

    #[Test]
    public function test_settings_page_parent_constructor_gets_called_and_setting_page_inherit_properties_and_methods(): void {
        $title = $this->faker->word();
        $template = $this->faker->slug();

        WP_Mock::userFunction('remove_accents')->twice()->with($title)->andReturn($title);
        WP_Mock::userFunction('sanitize_title_with_dashes')->twice()->with($title, '', 'save')->andReturn($title);

        $settings_page = SettingsPage::create($title, $template);
        $this->assertInstanceOf(SettingsPage::class, $settings_page);
        $this->assertInstanceOf(MenuPage::class, $settings_page);

        $this->assertSame($title, $settings_page->pageTitle());
        $this->assertSame($title, $settings_page->menuTitle());
        $this->assertSame('edit_posts', $settings_page->capability());
        $this->assertSame(config('plugin', 'name') . '-' . slugify($title), $settings_page->slug());
        $this->assertSame($template, $settings_page->template());
        $this->assertSame('', $settings_page->icon());
        $this->assertSame([], $settings_page->data());
        $this->assertNull($settings_page->position());

        $settings_page->setIcon('test123');
        $settings_page->with(['a' => 'b']);
        $this->assertSame('test123', $settings_page->icon());
        $this->assertSame(['a' => 'b'], $settings_page->data());
    }

    #[Test]
    public function test_setting_page_gets_built_by_build_method(): void {
        $menu_title = $this->faker->word();
        $template = $this->faker->slug();

        WP_Mock::userFunction('remove_accents')->once()->with($menu_title)->andReturn($menu_title);
        WP_Mock::userFunction('sanitize_title_with_dashes')->once()->with($menu_title, '', 'save')->andReturn($menu_title);

        $settings_page = SettingsPage::create($menu_title, $template);

        $settings_page->setIcon('test123');
        $settings_page->setCapability('test123');

        //HACK: uses a custom version of WP_Mock to successfully run.
        WP_Mock::expectActionAdded('admin_menu', WP_Mock\Functions::type(Closure::class));
        
        $settings_page->build();
        
        $this->assertTrue($settings_page->data()['page_slug'] === $settings_page->slug());
    }
}
