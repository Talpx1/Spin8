<?php

namespace Spin8\WP\Settings;

use Spin8\WP\MenuPage;

class SettingsPage extends MenuPage {

    /** @var array<SettingsSection|Setting> */
    protected array $settings = [];

    public static function create(string $menu_title, string $template): self {
        return new self($menu_title, $template);
    }

    private function __construct(string $menu_title, string $template) {
        parent::__construct($menu_title, $template);
    }

    /** @param callable(SettingsPage):array<SettingsSection|Setting> $settings */
    public function withSettings(callable $settings): void {//TODO: test
        $this->settings = $settings($this);
    }

    #[\Override]
    public function build(): static {
        $this->data['page_slug'] = $this->menu_slug;

        parent::build();

        foreach($this->settings as $setting_or_setting_section) {//TODO: test
            $setting_or_setting_section->register();        
        }
        
        return $this;
    }
}
