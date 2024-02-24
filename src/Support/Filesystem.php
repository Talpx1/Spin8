<?php declare(strict_types=1);

namespace Spin8\Support;

use function Safe\{symlink, readlink, copy, mkdir};

class Filesystem {

    /**
     * @internal originally made by @author Aidan Lister <aidan@php.net>
     * @internal @see @link https://www.aidanlister.com/2004/04/recursively-copying-directories-in-php/
     */
    public function copy(string $from, string $to): void { //TODO: test
        if (is_link($from)) {
            symlink(readlink($from), $to);
            return;
        }

        if (is_file($from)) {
            copy($from, $to);
            return;
        }

        if (!is_dir($to)) {
            mkdir($to);
        }

        $dir = dir($from);

        if($dir === false) {
            throw new \RuntimeException("Error while coping '{$from}' to '{$to}'. Please double check the permissions for '{$from}'.");
        }

        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            $this->copy($from.DIRECTORY_SEPARATOR.$entry, $to.DIRECTORY_SEPARATOR.$entry);
        }

        $dir->close();
    }
}