<?php

namespace Spin8\Settings\Enums;

enum SettingTypes: string {
        //wp default
    case STRING = 'string';
    case BOOL = 'boolean';
    case INT = 'integer';
    case NUMBER = 'number';
    case ARRAY = 'array';
    case OBJECT = 'object';
        //extra
    case COLOR = 'color';
    case EMAIL = 'email';
    case TEXTAREA = 'textarea';
    case URL = 'url';
    case SELECT = 'select';

    public function sanitizeCallback(): ?string {
        try {
            return match ($this) {
                self::STRING, self::BOOL, self::INT, self::NUMBER, self::SELECT => 'sanitize_text_field',
                self::COLOR => 'sanitize_hex_color',
                self::EMAIL => 'sanitize_email',
                self::TEXTAREA => 'sanitize_textarea_field',
                self::URL => 'sanitize_url',
            };
        } catch (\UnhandledMatchError $e) {
            return null;
        }
    }

    public function realValue(): string {
        try {
            return match ($this) {
                self::COLOR, self::EMAIL, self::TEXTAREA, self::URL, self::SELECT => 'string',
            };
        } catch (\UnhandledMatchError $e) {
            return $this->value;
        }
    }

    public function template(): ?array {
        try {
            return match ($this) {
                self::STRING => ['path' => 'partials/input', 'data' => ['type' => 'text']],
                self::BOOL => ['path' => 'partials/checkbox', 'data' => []],
                self::INT => ['path' => 'partials/input', 'data' => ['type' => 'number', 'step' => '1']],
                self::NUMBER => ['path' => 'partials/input', 'data' => ['type' => 'number', 'step' => '.01']],
                self::COLOR => ['path' => 'partials/input', 'data' => ['type' => 'color']],
                self::EMAIL => ['path' => 'partials/input', 'data' => ['type' => 'email']],
                self::TEXTAREA => ['path' => 'partials/textarea', 'data' => []],
                self::URL => ['path' => 'partials/input', 'data' => ['type' => 'url']],
                self::SELECT => ['path' => 'partials/select', 'data' => []],
            };
        } catch (\UnhandledMatchError $e) {
            return null;
        }
    }
}
