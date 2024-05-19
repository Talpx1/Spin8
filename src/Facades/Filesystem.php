<?php

declare(strict_types=1);

namespace Spin8\Facades;

/**
 * @mixin \Spin8\Support\Filesystem
 *
 * @method static void copy(string $from, string $to) 
 * @method static void requireIfExists(string $path, bool $use_require_once = false)
 * @method static void requireOnceIfExists(string $path)
 *
 * @see \Spin8\Support\Filesystem
 * @see \Spin8\Facades\Facade
 */
final class Filesystem extends Facade {
    /** @var string[] */
    protected static array $allowed = ['*'];

    protected static function implementor() : string {
        return 'support.filesystem';
    }
}
