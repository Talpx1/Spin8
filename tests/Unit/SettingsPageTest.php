<?php

namespace JsonLDForWP\Tests\Framework\Unit;

use JsonLDForWP\Framework\MenuPage;
use JsonLDForWP\Framework\Settings\SettingsPage;
use Mockery;
use Tests\TestCase;

use function Brain\Monkey\Actions\expectAdded;
use function Brain\Monkey\Functions\stubs;

/**
 * @coversDefaultClass \JsonLDForWP\Framework\Settings\SettingsPage
 */
class SettingsPageTest extends TestCase {

    /** 
     * @test  
     * @covers ::create
     */
    public function test_settings_page_object_gets_instantiated() {
        stubs(['sanitize_title']);
        $this->assertInstanceOf(SettingsPage::class, SettingsPage::create(self::$faker->word, self::$faker->slug));
    }

    /** 
     * @test  
     * @covers ::__construct
     */
    public function test_settings_page_parent_constructor_gets_called_and_setting_page_inherit_properties_and_methods() {
        stubs(['sanitize_title']);
        $title = self::$faker->word;
        $template = self::$faker->slug;
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

    /** 
     * @test  
     * @covers ::build
     */
    public function test_setting_page_gets_built_by_build_method() {
        stubs(['sanitize_title', '__']);
        $menu_title = self::$faker->word;
        $template = self::$faker->slug;
        $settings_page = SettingsPage::create($menu_title, $template);

        $settings_page->setIcon('test123');
        $settings_page->setCapability('test123');
        expectAdded('admin_menu')->once()->with(Mockery::type('Closure'));
        $settings_page->build();
        $this->assertTrue($settings_page->data()['page_slug'] === $settings_page->slug());
    }
}
