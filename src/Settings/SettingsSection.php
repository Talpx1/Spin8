<?php

namespace Spin8\Settings;

class SettingsSection {

    private string $title;
    private string $slug;
    private string|null $description = null;
    private string $page;

    public static function create(string $title, string $slug, string|SettingsPage $page): self { //TODO: add support for an enum with all the WP settings pages to be passed as $page
        return new self($title, $slug, $page);
    }

    private function __construct(string $title, string $slug, string|SettingsPage $page) { //TODO: add support for an enum with all the WP settings pages to be passed as $page
        $this->title = $title;
        $this->page = is_a($page, SettingsPage::class) ? $page->slug() : $page;
        $this->slug = config('plugin', 'name') . '-' . slugify($slug);
    }

    public function setDescription(string $description): self {
        $this->description = $description;
        return $this;
    }

    public function title(): string {
        return $this->title;
    }

    public function slug(): string {
        return $this->slug;
    }

    public function page(): string {
        return $this->page;
    }

    public function description(): string {
        return $this->description;
    }

    public function register(): self {
        add_action("admin_init", fn () => add_settings_section(
            $this->slug,
            $this->title,
            isset($this->description) ? (fn () => $this->description) : null,
            $this->page
        ));

        return $this;
    }
}
