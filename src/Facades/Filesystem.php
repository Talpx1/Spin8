<?php

declare(strict_types=1);

namespace Spin8\Facades;

/**
 * @mixin \Spin8\Support\Filesystem
 *
 * @method static string copyDir(string $from, string $to) 
 *
 * @see \Spin8\Support\Filesystem
 * @see \Spin8\Facades\Facade
 */
final class Filesystem extends Facade { //TODO: test
    /** @var string[] */
    protected static array $allowed = ['*'];

    protected static function implementor() : string {
        return 'support.filesystem';
    }
}
