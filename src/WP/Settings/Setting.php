<?php

namespace Spin8\WP\Settings;

use Closure;
use InvalidArgumentException;
use Spin8\WP\Settings\Enums\SettingsGroups;
use Spin8\WP\Settings\Enums\SettingTypes;
use RuntimeException;
use Spin8\Guards\GuardAgainstEmptyParameter;
use TypeError;

class Setting {
    /**
     * @var array<string, mixed>
     */
    private array $data = [];
    private string $section;
    private string $page;
    private string $name;
    private string $title;
    private ?string $type = null;
    private ?string $description = null;
    private ?bool $show_in_rest = null;
    private mixed $default = null;
    private string|Closure|null $sanitize_callback = null;
    private string $class = '';
    private ?string $template = null;

    public static function create(SettingsSection|SettingsGroups|string $section, string $title, string $name): self {
        return new self($section, $title, $name);
    }

    private function __construct(SettingsSection|SettingsGroups|string $section, string $title, string $name) {
        GuardAgainstEmptyParameter::check($title);
        GuardAgainstEmptyParameter::check($name);
        
        $this->setSectionAndPage($section);
        $this->title = $title;
        $this->name = config('plugin.name') . '-' . slugify($name);
        
    }

    private function setSectionAndPage(SettingsSection|SettingsGroups|string $section):void {
        if(is_string($section)) {
            GuardAgainstEmptyParameter::check($section);

            $this->section = $section;
            $this->page = $section;
            return;
        }

        if (is_a($section, SettingsGroups::class)) {
            $this->section = $section->value;
            $this->page = $section->value;
            return;
        }

        if (is_a($section, SettingsSection::class)) {
            $this->section = $section->slug();
            $this->page = $section->page();
            return;
        }

        throw new InvalidArgumentException("section must be a SettingsSection instance or a SettingsGroups instance or a string. ".gettype($section)." passed. Should never be thrown anyway...");
    }

    public function setType(SettingTypes $type): self {
        if (isset($this->default) && gettype($this->default) !== $type->realValue()) {
            throw new TypeError("The specified type {$type->realValue()} ({$type->name}) is not compatible with the already set default ".gettype($this->default).". Please change the default or the type.");
        }

        $this->type = $type->realValue();
        $this->sanitize_callback = $type->sanitizeCallback();
        $this->template = $type->template()['path'] ?? null;
        $this->data = $type->template()['data'] ?? [];
        return $this;
    }

    //TODO: add support for SettingPagesEnum and SettingPage instance
    public function setPage(string $page): self {
        $this->page = $page;
        return $this;
    }

    public function setDescription(string $description): self {
        $this->description = $description;
        return $this;
    }

    public function setDefault(mixed $default): self {
        if (isset($this->type) && gettype($default) !== $this->type) {
            throw new TypeError("The type of the default value does not match the type specified for the setting. Default value type is ".gettype($default).", the specified type is {$this->type}.");
        }

        $this->default = $default;
        return $this;
    }

    public function setShowInRest(bool $show_in_rest = true): self {
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

    public function setSanitizeCallback(string|callable $callback): self {
        if (is_string($callback) && !function_exists($callback)) {
            throw new RuntimeException("Invalid sanitize callback: a function named {$callback} can not be found.");
        }

        $this->sanitize_callback = is_string($callback) ? $callback : Closure::fromCallable($callback);
        return $this;
    }

    /**
     *
     * @param array<string, mixed> $data
     */
    public function with(array $data): self {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function name(): string {
        return $this->name;
    }

    public function type(): ?string {
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

    public function description(): ?string {
        return $this->description;
    }

    public function sanitizeCallback(): string|Closure|null {
        return $this->sanitize_callback;
    }

    public function template(): ?string {
        return $this->template;
    }

    /**
     * @return array<string, mixed> $data
     */
    public function data(): ?array {
        return $this->data;
    }

    public function default(): mixed {
        return $this->default;
    }

    public function class(): string {
        return $this->class;
    }

    public function showInRest(): ?bool {
        return $this->show_in_rest;
    }

    public function register(): self {

        if (!isset($this->template)) {
            throw new RuntimeException("No setting template defined or available. Sometimes templates gets automatically set when specifying the type (setType) of the option. Alternatively define a template calling the `setTemplate(path, data)` method on a Setting instance.");
        }

        $args = [];

        foreach(['type', 'description', 'show_in_rest', 'default', 'sanitize_callback'] as $arg){
            if(is_null($this->{$arg})) {continue;}
            
            $args[$arg] = $this->{$arg};
        }

        add_action("admin_init", fn () => register_setting($this->page, $this->name, $args));

        add_action("admin_init", fn () => add_settings_field(
            $this->name,
            $this->title,
            fn ($args) => adminAsset($this->template, array_merge($args, $this->data, ['current' => get_option($this->name)])),
            $this->page,
            $this->section,
            ['label_for' => $this->name, 'class' => $this->class]
        ));

        return $this;
    }
}
