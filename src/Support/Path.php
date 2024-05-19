<?php declare(strict_types=1);

namespace Spin8\Support;

class Path {

    /** @var string[] */
    protected const array SEPARATORS = ["/", "\\"];

    public function append(string $from, string $append = ""): string {
        $append = self::removeExtremitySeparators($append);
        $from = self::removeEndingSeparator($from);

        $path = $from;
        
        if(!empty($append)){
            $path .= DIRECTORY_SEPARATOR . $append;
        }

        return self::correctSeparators($path);
    }

    public function correctSeparators(string $path): string {
        return str_replace(self::SEPARATORS, DIRECTORY_SEPARATOR, $path);
    }

    public function removeBeginningSeparator(string $path): string {
        return ltrim($path, implode(self::SEPARATORS));
    }

    public function removeEndingSeparator(string $path): string {
        return rtrim($path, implode(self::SEPARATORS));
    }

    public function removeExtremitySeparators(string $path): string {
        return trim($path, implode(self::SEPARATORS));
    }

}