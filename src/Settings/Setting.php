<?php

namespace Spin8\Settings;

use Spin8\Settings\Enums\SettingsGroups;
use Spin8\Settings\Enums\SettingTypes;
use Spin8\Spin8;
use RuntimeException;
use TypeError;

class Setting {

    private string $section;
    private string $page;
    private string $name;
    private string $title;
    private string|null $type = null;
    private string|null $description = null;
    private bool|null $show_in_rest = null;
    private mixed $default = null;
    private string|null $sanitize_callback = null;
    private string $class = '';
    private string|null $template = null;
    private array $data = [];

    public static function create(SettingsSection|SettingsGroups|string $section, string $title, string $name): self {
        return new self($section, $title, $name);
    }

    private function __construct(SettingsSection|SettingsGroups|string $section, string $title, string $name) {
        $this->title = $title;
        $this->name = config('plugin', 'name') . '-' . slugify($name);

        if (is_a($section, SettingsGroups::class)) {
            $this->section = $section->value;
            $this->page = $section->value;
        } elseif (is_a($section, SettingsSection::class)) {
            $this->section = $section->slug();
            $this->page = $section->page();
        } else {
            $this->section = $section;
            $this->page = $section;
        }
    }

    public function setType(SettingTypes $type): self {
        if (isset($this->default) && gettype($this->default) !== $type->realValue())
            throw new TypeError(sprintf(__("The specified type %s (%s) is not compatible with the already set default %s. Please change the default or the type."), $type->realValue(), $type->name, gettype($this->default)));

        $this->type = $type->realValue();
        $this->sanitize_callback = $type->sanitizeCallback();
        $this->template = $type->template()['path'] ?? null;
        $this->data = $type->template()['data'] ?? [];
        return $this;
    }

    public function setPage(string $page): self { //TODO: add support for SettingPagesEnum and SettingPage instance
        $this->page = $page;
        return $this;
    }

    public function setDescription(string $description): self {
        $this->description = $description;
        return $this;
    }

    public function setDefault(mixed $default): self {
        if (isset($this->type) && gettype($default) !== $this->type) throw new TypeError(sprintf(__("The type of the default value does not match the type specified fot the setting. Default value type is %s, the specified type is %s."), gettype($default), $this->type));

        $this->default = $default;
        return $this;
    }

    public function setShowInRest(bool $show_in_rest): self {
        $this->show_in_rest = $show_in_rest;
        return $this;
    }

    public function setTemplate(string $path): self {
        $this->template = $path;
        return $this;
    }

    public function setClass(string $class): self {
        $this->class = $class;
        return $this;
    }

    public function with(array $data): self {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function name(): string {
        return $this->name;
    }

    public function type(): string|null {
        return $this->type;
    }

    public function title(): string {
        return $this->title;
    }

    public function section(): string {
        return $this->section;
    }

    public function page(): string {
        return $this->page;
    }

    public function description(): string|null {
        return $this->description;
    }

    public function sanitizeCallback(): string|null {
        return $this->sanitize_callback;
    }

    public function template(): string|null {
        return $this->template;
    }

    public function data(): array {
        return $this->data;
    }

    public function default(): mixed {
        return $this->default;
    }

    public function class(): string {
        return $this->class;
    }

    public function showInRest(): bool|null {
        return $this->show_in_rest;
    }

    public function register(callable|string|null $sanitize_callback = null): self {
        $args = [];
        if (isset($this->type)) $args['type'] = $this->type;
        if (isset($this->description)) $args['description'] = $this->description;

        if (isset($sanitize_callback)) {
            if (is_string($sanitize_callback) && !function_exists($sanitize_callback)) throw new RuntimeException(sprintf(__("Invalid sanitize callback: a function named %s can not be found."), $sanitize_callback));
            $args['sanitize_callback'] = $sanitize_callback;
        } elseif (isset($this->sanitize_callback)) $args['sanitize_callback'] = $this->sanitize_callback;

        if (isset($this->show_in_rest)) $args['show_in_rest'] = $this->show_in_rest;
        if (isset($this->default)) $args['default'] = $this->default;

        add_action("admin_init", fn () => register_setting($this->page, $this->name, $args));

        if (!isset($this->template))
            throw new RuntimeException(__("No setting template defined or available. Sometimes templates gets automatically set when specifying the type (setType) of the option. Alternatively define a template calling the 'setTemplate(path, data)' method on a Setting instance."));

        add_action("admin_init", fn () => add_settings_field(
            $this->name,
            $this->title,
            fn ($args) => admin_asset($this->template, array_merge($args, $this->data, ['current' => get_option($this->name)])),
            $this->page,
            $this->section,
            ['label_for' => $this->name, 'class' => $this->class]
        ));

        return $this;
    }
}
