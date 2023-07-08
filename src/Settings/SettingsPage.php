<?php

namespace Spin8\Settings;

use Spin8\MenuPage;

class SettingsPage extends MenuPage {

    public static function create(string $menu_title, string $template): self {
        return new self($menu_title, $template);
    }

    private function __construct(string $menu_title, string $template) {
        parent::__construct($menu_title, $template);
    }

    public function build(): self {
        $this->data['page_slug'] = $this->menu_slug;
        parent::build();
        return $this;
    }
}
