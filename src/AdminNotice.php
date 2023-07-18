<?php declare(strict_types=1);

namespace Spin8;

class AdminNotice {
    private string $text;
    private ?string $type = null;
    private bool $dismissible = false;

    public static function create(string $text): self {
        return new self($text);
    }

    //TODO: create AdminNoticeType enum
    public static function error(string $text): self {
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

    public function type(): ?string {
        return $this->type;
    }

    public function text(): string {
        return $this->text;
    }

    public function setDismissible(bool $dismissible = true): self {
        $this->dismissible = $dismissible;
        return $this;
    }

    public function isDismissible(): bool {
        return $this->dismissible;
    }

    public function render(): void {
        add_action("admin_notices", fn () => adminAsset('partials/notice', ['notice' => $this]));
    }
}
