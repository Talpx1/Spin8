<?php

namespace Spin8;

class AdminNotice {
    private string $text;
    private string|null $type = null;
    private bool $dismissible = false;

    public static function create(string $text): self {
        return new self($text);
    }

    public static function error(string $text): self { //TODO: create AdminNoticeType enum
        return (new self($text))->setType('error');
    }

    public static function success(string $text): self {
        return (new self($text))->setType('success');
    }

    private function __construct(string $text) {
        $this->text = $text;
    }

    private function setType(string $type): self {
        $this->type = $type;
        return $this;
    }

    public function type(): string|null {
        return $this->type;
    }

    public function text(): string {
        return $this->text;
    }

    public function setDismissible($dismissible = true): self {
        $this->dismissible = $dismissible;
        return $this;
    }

    public function isDismissible(): bool {
        return $this->dismissible;
    }

    public function render(): void {
        add_action("admin_notices", fn () => admin_asset('partials/notice', ['notice' => $this]));
    }
}
