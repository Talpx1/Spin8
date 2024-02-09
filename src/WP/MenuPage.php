<?php declare(strict_types=1);

namespace Spin8\WP;

class MenuPage {
    /**
     * @var array<string, mixed>
     */
    protected array $data = [];
    protected string $page_title;
    protected string $menu_title;
    protected string $capability = "edit_posts";
    protected string $menu_slug;
    protected string $template;
    protected string $icon_url = '';
    protected ?int $position = null;

    public static function create(string $menu_title, string $template): self {
        return new self($menu_title, $template);
    }

    protected function __construct(string $menu_title, string $template) {
        $this->menu_title = $menu_title;
        $this->page_title = $menu_title;
        $this->menu_slug = config('plugin', 'name') . '-' . slugify($menu_title);
        $this->template = $template;
    }

    public function setPageTitle(string $page_title): self {
        $this->page_title = $page_title;
        return $this;
    }

    public function setCapability(string $capability): self {
        $this->capability = $capability;
        return $this;
    }

    public function setSlug(string $menu_slug): self {
        $this->menu_slug = config('plugin', 'name') . '-' . slugify($menu_slug);
        return $this;
    }

    public function setIcon(string $icon_url): self {
        $this->icon_url = $icon_url;
        return $this;
    }

    public function setPosition(int $position): self {
        $this->position = $position;
        return $this;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function with(array $data): self {
        $this->data = $data;
        return $this;
    }

    public function slug(): string {
        return $this->menu_slug;
    }

    public function pageTitle(): string {
        return $this->page_title;
    }

    public function menuTitle(): string {
        return $this->menu_title;
    }

    public function capability(): string {
        return $this->capability;
    }

    public function template(): string {
        return $this->template;
    }

    public function icon(): string {
        return $this->icon_url;
    }

    public function position(): ?int {
        return $this->position;
    }

    /**
     * @return array<string, mixed>
     */
    public function data(): array {
        return $this->data;
    }

    public function build(): self {
        add_action('admin_menu', function() {
            add_menu_page(
                $this->page_title,
                $this->menu_title,
                $this->capability,
                $this->menu_slug,
                fn () => adminAsset($this->template, $this->data),
                $this->icon_url,
                $this->position,
            );
        });

        return $this;
    }
}
