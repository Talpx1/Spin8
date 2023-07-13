<?php

namespace Spin8\Tests\Unit;

use Closure;
use PHPUnit\Framework\Attributes\Test;
use Spin8\MenuPage;
use Spin8\Settings\SettingsPage;
use Mockery;
use Spin8\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use WP_Mock;
use function Brain\Monkey\Actions\expectAdded;
use function Brain\Monkey\Functions\stubs;

#[CoversClass(SettingsPage::class)]
class SettingsPageTest extends TestCase {

    #[Test]
    public function test_settings_page_object_gets_instantiated() {
        $menu_title = $this->faker->word();
        WP_Mock::userFunction('sanitize_title')->once()->with($menu_title)->andReturn($menu_title);

        $this->assertInstanceOf(SettingsPage::class, SettingsPage::create($menu_title, $this->faker->slug()));
    }

    #[Test]
    public function test_settings_page_parent_constructor_gets_called_and_setting_page_inherit_properties_and_methods() {
        $title = $this->faker->word();
        $template = $this->faker->slug();

        WP_Mock::userFunction('sanitize_title')->twice()->with($title)->andReturn($title);

        $settings_page = SettingsPage::create($title, $template);
        $this->assertInstanceOf(SettingsPage::class, $settings_page);
        $this->assertInstanceOf(MenuPage::class, $settings_page);

        $this->assertTrue($settings_page->pageTitle() === $title);
        $this->assertTrue($settings_page->menuTitle() === $title);
        $this->assertTrue($settings_page->capability() === 'edit_posts');
        $this->assertTrue($settings_page->slug() === config('plugin', 'name') . '-' . slugify($title));
        $this->assertTrue($settings_page->template() === $template);
        $this->assertTrue($settings_page->icon() === '');
        $this->assertTrue($settings_page->data() === []);
        $this->assertNull($settings_page->position());

        $settings_page->setIcon('test123');
        $settings_page->with(['a' => 'b']);
        $this->assertTrue($settings_page->icon() === 'test123');
        $this->assertTrue($settings_page->data() === ['a' => 'b']);
    }

    #[Test]
    public function test_setting_page_gets_built_by_build_method() {
        $menu_title = $this->faker->word();
        $template = $this->faker->slug();

        WP_Mock::userFunction('sanitize_title')->once()->with($menu_title)->andReturn($menu_title);

        $settings_page = SettingsPage::create($menu_title, $template);

        $settings_page->setIcon('test123');
        $settings_page->setCapability('test123');

        //FIXME: fails because of a bug in WP_Mock. Pull request with fix already sent.
        WP_Mock::expectActionAdded('admin_menu', WP_Mock\Functions::type(Closure::class));
        
        $settings_page->build();
        
        $this->assertTrue($settings_page->data()['page_slug'] === $settings_page->slug());
    }
}
