<?php declare(strict_types=1);

namespace Spin8;

final class Spin8{

    private array $singletones = [];
    private static ?self $instance = null;
    
    private function __construct() {}
    
    public static function instance(): self {
        if(is_null(self::$instance)) self::$instance = new self();
        return self::$instance; 
    }

    public function singletone(string $class): mixed {
        if(! array_key_exists($class, $this->singletones)) $this->singletones[$class] = $class::instance();  

        return $this->singletones[$class];
    }
}