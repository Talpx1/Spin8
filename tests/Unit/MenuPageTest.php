<?php

namespace Spin8\Tests\Unit;

use Spin8\MenuPage;
use Mockery;
use Spin8\Tests\TestCase;

use function Brain\Monkey\Actions\expectAdded;
use function Brain\Monkey\Functions\stubs;

/**
 * @coversDefaultClass \Spin8\MenuPage
 */
class MenuPageTest extends TestCase {

    /** 
     * @test  
     * @covers ::create
     */
    public function test_settings_page_object_gets_instantiated() {
        stubs(['sanitize_title']);
        $this->assertInstanceOf(MenuPage::class, MenuPage::create(self::$faker->word, self::$faker->slug));
    }

    /** 
     * @test  
     * @covers ::__construct
     * @covers ::menuTitle
     * @covers ::pageTitle
     */
    public function test_page_menu_title_and_page_title_gets_initialized() {
        stubs(['sanitize_title']);
        $title = self::$faker->word;
        $menu_page = MenuPage::create($title, self::$faker->slug);
        $this->assertTrue($title === $menu_page->pageTitle());
        $this->assertTrue($title === $menu_page->menuTitle());
    }

    /** 
     * @test  
     * @covers ::__construct
     * @covers ::slug
     */
    public function test_page_menu_slug_gets_initialized() {
        stubs(['sanitize_title']);
        $title = self::$faker->word;
        $menu_page = MenuPage::create($title, self::$faker->slug);
        $this->assertTrue(config('plugin', 'name') . '-' . slugify($title) === $menu_page->slug());
    }

    /** 
     * @test  
     * @covers ::__construct
     * @covers ::template
     */
    public function test_page_template_gets_initialized() {
        stubs(['sanitize_title']);
        $template = self::$faker->slug;
        $menu_page = MenuPage::create(self::$faker->word, $template);
        $this->assertTrue($template === $menu_page->template());
    }

    /** 
     * @test  
     * @covers ::setPageTitle
     * @covers ::pageTitle
     */
    public function test_page_title_gets_set_in_set_page_title_method() {
        $title = self::$faker->word;
        stubs(['sanitize_title']);
        $menu_page = MenuPage::create(self::$faker->word, self::$faker->slug);
        $menu_page->setPageTitle($title);
        $this->assertTrue($title === $menu_page->pageTitle());
    }

    /** 
     * @test  
     * @covers ::setCapability
     * @covers ::capability
     */
    public function test_page_capability_gets_set_in_set_capability_method() {
        $capability = self::$faker->slug;
        stubs(['sanitize_title']);
        $menu_page = MenuPage::create(self::$faker->word, self::$faker->slug);
        $this->assertTrue($menu_page->capability() === 'edit_posts');
        $menu_page->setCapability($capability);
        $this->assertTrue($capability === $menu_page->capability());
    }

    /** 
     * @test  
     * @covers ::setSlug
     * @covers ::slug
     */
    public function test_page_menu_slug_gets_set_in_set_slug_method() {
        $slug = self::$faker->slug;
        $title = self::$faker->word;
        stubs(['sanitize_title']);
        $menu_page = MenuPage::create($title, self::$faker->slug);
        $this->assertTrue($menu_page->slug() === config('plugin', 'name') . '-' . slugify($title));
        $menu_page->setSlug($slug);
        $this->assertTrue($menu_page->slug() === config('plugin', 'name') . '-' . slugify($slug));
    }

    /** 
     * @test  
     * @covers ::setIcon
     * @covers ::icon
     */
    public function test_page_icon_gets_set_in_set_icon_method() {
        stubs(['sanitize_title']);
        $menu_page = MenuPage::create(self::$faker->word, self::$faker->slug);
        $this->assertEmpty($menu_page->icon());
        $this->assertIsString($menu_page->icon());
        $icon = self::$faker->imageUrl();
        $menu_page->setIcon($icon);
        $this->assertTrue($menu_page->icon() === $icon);
    }

    /** 
     * @test  
     * @covers ::setPosition
     * @covers ::position
     */
    public function test_page_menu_position_gets_set_in_set_position_method() {
        stubs(['sanitize_title']);
        $menu_page = MenuPage::create(self::$faker->word, self::$faker->slug);
        $this->assertNull($menu_page->position());
        $menu_page->setPosition(2);
        $this->assertIsInt($menu_page->position());
        $this->assertTrue($menu_page->position() === 2);
    }

    /** 
     * @test  
     * @covers ::with
     * @covers ::data
     */
    public function test_page_data_gets_set_in_with_method() {
        stubs(['sanitize_title']);
        $menu_page = MenuPage::create(self::$faker->word, self::$faker->slug);
        $this->assertIsArray($menu_page->data());
        $this->assertEmpty($menu_page->data());
        $menu_page->with(["test" => "test_123"]);
        $this->assertIsArray($menu_page->data());
        $this->assertNotEmpty($menu_page->data());
        $this->assertTrue($menu_page->data() === ["test" => "test_123"]);
    }

    /** 
     * @test  
     * @covers ::build
     */
    public function test_page_gets_built_by_build_method() {
        stubs(['sanitize_title', '__']);
        $menu_title = self::$faker->word;
        $template = self::$faker->slug;
        $menu_page = MenuPage::create($menu_title, $template);

        $menu_page->setIcon('test123');
        $menu_page->setCapability('test123');
        expectAdded('admin_menu')->once()->with(Mockery::type('Closure'));
        $menu_page->build();
    }
}
