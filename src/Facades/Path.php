<?php

declare(strict_types=1);

namespace Spin8\Facades;

use Spin8\Support\Path as PathSupport;

/**
 * @mixin \Spin8\Support\Path
 *
 * @method static string append(string $from, string $append = "")
 * @method static string correctSeparators(string $path)
 * @method static string removeBeginningSeparator(string $path)
 * @method static string removeEndingSeparator(string $path)
 * @method static string removeExtremitySeparators(string $path)
 *
 * @see \Spin8\Support\Path
 * @see \Spin8\Facades\Facade
 */
final class Path extends Facade { ///TODO: test
    /** @var string[] */
    protected static array $allowed = ['*'];

    protected static function implementor() : string {
        return 'support.path';
    }
}
