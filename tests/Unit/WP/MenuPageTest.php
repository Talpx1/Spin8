<?php

namespace Spin8\Tests\Unit\WP;

use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spin8\WP\MenuPage;
use Spin8\Tests\TestCase;

use WP_Mock;

#[CoversClass(MenuPage::class)]
class MenuPageTest extends TestCase {

    #[Test]
    public function test_settings_page_object_gets_instantiated(): void {
        $menu_title = $this->faker->word();
        WP_Mock::userFunction('remove_accents')->once()->with($menu_title)->andReturn($menu_title);
        WP_Mock::userFunction('sanitize_title_with_dashes')->once()->with($menu_title, '', 'save')->andReturn($menu_title);
        $this->assertInstanceOf(MenuPage::class, MenuPage::create($menu_title, $this->faker->slug()));
    }

    #[Test]
    public function test_page_menu_title_and_page_title_gets_initialized(): void {
        $title = $this->faker->word();
        WP_Mock::userFunction('remove_accents')->once()->with($title)->andReturn($title);
        WP_Mock::userFunction('sanitize_title_with_dashes')->once()->with($title, '', 'save')->andReturn($title);
        $menu_page = MenuPage::create($title, $this->faker->slug());
        $this->assertTrue($title === $menu_page->pageTitle());
        $this->assertTrue($title === $menu_page->menuTitle());
    }

    #[Test]
    public function test_page_menu_slug_gets_initialized(): void {
        $title = $this->faker->word();
        WP_Mock::userFunction('remove_accents')->twice()->with($title)->andReturn($title);
        WP_Mock::userFunction('sanitize_title_with_dashes')->twice()->with($title, '', 'save')->andReturn($title);
        $menu_page = MenuPage::create($title, $this->faker->slug());
        $this->assertTrue(config('plugin', 'name') . '-' . slugify($title) === $menu_page->slug());
    }

    #[Test]
    public function test_page_template_gets_initialized(): void {
        $template = $this->faker->slug();
        $title = $this->faker->word();
        WP_Mock::userFunction('remove_accents')->once()->with($title)->andReturn($title);
        WP_Mock::userFunction('sanitize_title_with_dashes')->once()->with($title, '', 'save')->andReturn($title);
        $menu_page = MenuPage::create($title, $template);
        $this->assertTrue($template === $menu_page->template());
    }

    #[Test]
    public function test_page_title_gets_set_in_set_page_title_method(): void {
        $page_title = $this->faker->word();
        $menu_title = $this->faker->word();
        WP_Mock::userFunction('remove_accents')->once()->with($menu_title)->andReturn($menu_title);
        WP_Mock::userFunction('sanitize_title_with_dashes')->once()->with($menu_title, '', 'save')->andReturn($menu_title);
        $menu_page = MenuPage::create($menu_title, $this->faker->slug());
        $menu_page->setPageTitle($page_title);
        $this->assertTrue($page_title === $menu_page->pageTitle());
    }

    #[Test]
    public function test_page_capability_gets_set_in_set_capability_method(): void {
        $capability = $this->faker->slug();
        $menu_title = $this->faker->word();
        WP_Mock::userFunction('remove_accents')->once()->with($menu_title)->andReturn($menu_title);
        WP_Mock::userFunction('sanitize_title_with_dashes')->once()->with($menu_title, '', 'save')->andReturn($menu_title);
        $menu_page = MenuPage::create($menu_title, $this->faker->slug());
        $this->assertTrue($menu_page->capability() === 'edit_posts');
        $menu_page->setCapability($capability);
        $this->assertTrue($capability === $menu_page->capability());
    }

    #[Test]
    public function test_page_menu_slug_gets_set_in_set_slug_method(): void {
        $title = $this->faker->word();
        
        WP_Mock::userFunction('remove_accents')->twice()->with($title)->andReturn($title);
        WP_Mock::userFunction('sanitize_title_with_dashes')->twice()->with($title, '', 'save')->andReturn($title);
        $menu_page = MenuPage::create($title, $this->faker->slug());
        
        $this->assertTrue($menu_page->slug() === config('plugin', 'name') . '-' . slugify($title));
        
        $slug = $this->faker->slug();

        WP_Mock::userFunction('remove_accents')->twice()->with($slug)->andReturn($slug);
        WP_Mock::userFunction('sanitize_title_with_dashes')->twice()->with($slug, '', 'save')->andReturn($slug);
        $menu_page->setSlug($slug);
        
        $this->assertTrue($menu_page->slug() === config('plugin', 'name') . '-' . slugify($slug));
    }

    #[Test]
    public function test_page_icon_gets_set_in_set_icon_method(): void {
        $title = $this->faker->word();        
        WP_Mock::userFunction('remove_accents')->once()->with($title)->andReturn($title);
        WP_Mock::userFunction('sanitize_title_with_dashes')->once()->with($title, '', 'save')->andReturn($title);

        $menu_page = MenuPage::create($title, $this->faker->slug());

        $this->assertEmpty($menu_page->icon());
        $this->assertIsString($menu_page->icon());

        $icon = $this->faker->imageUrl();

        $menu_page->setIcon($icon);

        $this->assertTrue($menu_page->icon() === $icon);
    }

    #[Test]
    public function test_page_menu_position_gets_set_in_set_position_method(): void {
        $title = $this->faker->word();        
        WP_Mock::userFunction('remove_accents')->once()->with($title)->andReturn($title);
        WP_Mock::userFunction('sanitize_title_with_dashes')->once()->with($title, '', 'save')->andReturn($title);

        $menu_page = MenuPage::create($title, $this->faker->slug());

        $this->assertNull($menu_page->position());
        
        $menu_page->setPosition(2);
        
        $this->assertIsInt($menu_page->position());
        $this->assertSame($menu_page->position(), 2);
    }

    #[Test]
    public function test_page_data_gets_set_in_with_method(): void {
        $title = $this->faker->word();        
        WP_Mock::userFunction('remove_accents')->once()->with($title)->andReturn($title);
        WP_Mock::userFunction('sanitize_title_with_dashes')->once()->with($title, '', 'save')->andReturn($title);

        $menu_page = MenuPage::create($title, $this->faker->slug());

        $this->assertIsArray($menu_page->data());
        $this->assertEmpty($menu_page->data());
        
        $menu_page->with(["test" => "test_123"]);
        
        $this->assertIsArray($menu_page->data());
        $this->assertNotEmpty($menu_page->data());
        $this->assertSame($menu_page->data(), ["test" => "test_123"]);
    }

    #[Test]
    public function test_page_gets_built_by_build_method(): void {        
        $menu_title = $this->faker->word();
        $template = $this->faker->slug();

        WP_Mock::userFunction('remove_accents')->once()->with($menu_title)->andReturn($menu_title);
        WP_Mock::userFunction('sanitize_title_with_dashes')->once()->with($menu_title, '', 'save')->andReturn($menu_title);

        $menu_page = MenuPage::create($menu_title, $template);

        $menu_page->setIcon('test123');
        $menu_page->setCapability('test123');

        //HACK: uses a custom version of WP_Mock to successfully run.
        WP_Mock::expectActionAdded('admin_menu', WP_Mock\Functions::type(Closure::class));
        
        $menu_page->build();
    }
}
