<?php declare(strict_types=1);

namespace Spin8\Support;

use Spin8\Guards\GuardAgainstEmptyParameter;
use function Safe\symlink;
use function Safe\readlink;
use function Safe\copy;
use function Safe\mkdir;

class Filesystem {

    /**
     * Copies a source path (file or directory) to a destination path
     * 
     * @param string $from path to copy
     * @param string $to destination path, where to copy
     * 
     * @internal originally made by @author Aidan Lister <aidan@php.net>
     * @internal @see @link https://www.aidanlister.com/2004/04/recursively-copying-directories-in-php/
     */
    public function copy(string $from, string $to): void {
        GuardAgainstEmptyParameter::check($from);
        GuardAgainstEmptyParameter::check($to);

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

        $dir = @dir($from);

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

    /**
     * Require a file only if exists.
     * 
     * @param string $path path of the file to require.
     * @param bool $use_require_once if true require_once will be used, require otherwise. Default false (require)
     */
    public function requireIfExists(string $path, bool $use_require_once = false): void {
        GuardAgainstEmptyParameter::check($path);

        if(!file_exists($path)) {
            return;
        }

        $use_require_once ? require_once $path : require $path;
    }

    /**
     * Require a file once only if exists.
     * 
     * @param string $path path of the file to require.    
     */
    public function requireOnceIfExists(string $path): void {
        GuardAgainstEmptyParameter::check($path);

        $this->requireIfExists($path, true);
    }
}