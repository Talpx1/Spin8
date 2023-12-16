<?php declare(strict_types=1);

namespace Spin8\Container\Traits;

use BadMethodCallException;
use Closure;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

trait CallableSupport {
    
    /** 
     * @param string|array{string|object, string}|callable $callable
     * @param array<string|int, mixed>|array{} $params 
     */
    public function call(callable|string|array $callable, array $params = []): mixed {
        if(is_string($callable)) {
            if(str_contains($callable, "@")) {
                [$class_name, $method] = explode("@", $callable);
                $callable = [$this->get($class_name), $method];
            } else if(str_contains($callable, "::")) {
                $callable = explode("::", $callable);
            } else {
                return $callable(...$params);
            }
        }

        foreach(array_keys($params) as $key) {
            if(!is_string($key)) {
                throw new InvalidArgumentException("\$param array parameter passed to call should be an associative array, where the keys are the parameter name.");
            }
        }

        /** @var array{string, mixed} $params */

        if(is_array($callable)) {
            $callable_class = is_object($callable[0]) ? $callable[0]::class : $callable[0];
            
            if(!method_exists(...$callable)) {
                throw new BadMethodCallException("Container is unable to call method {$callable[1]} as it does not exists in class {$callable_class}");
            }
            
            $reflector = new ReflectionMethod(...$callable);

            $method_doc_comment = $reflector->getDocComment();
            
            return call_user_func_array($callable, $this->resolveFunctionParams($reflector, $callable_class, $method_doc_comment, $params));            
        }

        if(is_callable($callable)) {
            $reflector = new ReflectionFunction($callable);

            return call_user_func_array($callable, $this->resolveFunctionParams($reflector, Closure::class, params: $params));
        }

        throw new InvalidArgumentException("The callable passed to ".__FUNCTION__." can not be resolved.");
    }

    /** 
     * @param array<string, mixed>|array{} $params 
     * @param class-string $id
     * @return array<string, mixed>
     */
    protected function resolveFunctionParams(ReflectionFunctionAbstract $reflector, string $id, string|false $doc_comment = false, array $params = []): array {
        foreach($reflector->getParameters() as $param) {
            if(array_key_exists($param->getName(), $params)) {
                continue;
            }

            $params[$param->getName()] = $this->resolveDependencies([$param], $doc_comment, $id)[0];
        }

        return $params;
    }

}