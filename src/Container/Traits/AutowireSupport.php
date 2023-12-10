<?php

declare(strict_types=1);

namespace Spin8\Container\Traits;

use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use Spin8\Container\Exceptions\AutowiringFailureException;
use Spin8\Guards\GuardAgainstNonExistingClassString;

trait AutowireSupport {
    /** @param class-string $id */
    protected function autowire(string $id) : mixed {
        // @phpstan-ignore-next-line
        GuardAgainstNonExistingClassString::check($id, fn (string $message) => new AutowiringFailureException($id, $message));

        $reflection_class = new ReflectionClass($id);

        $constructor = $reflection_class->getConstructor();

        if (is_null($constructor)) {
            return new $id();
        }

        $constructor_params = $constructor->getParameters();

        if (empty($constructor_params)) {
            return new $id();
        }

        $constructor_doc_comment = $constructor->getDocComment();

        $dependencies = $this->resolveDependencies($constructor_params, $constructor_doc_comment, $id);

        return $reflection_class->newInstanceArgs($dependencies);
    }

    /**
     * @param ReflectionParameter[] $parameters
     * @param class-string $id
     * @return object[]
     */
    protected function resolveDependencies(array $parameters, string|false $doc_comment, string $id) : array {
        return array_map(function (ReflectionParameter $param) use ($id, $doc_comment) {
            $name = $param->getName();
            $type = $param->getType();

            try {
                $resolved = $this->maybeResolveDependencyFromConfig($type);
                if (false !== $resolved) {
                    return $resolved;
                }

                if (is_null($type)) {
                    $resolved = $this->maybeResolveDependencyFromAnnotation($name, $doc_comment, $id);
                    if (false !== $resolved) {
                        return $resolved;
                    }

                    throw new AutowiringFailureException($id, "{$id} uses a non type-hinted parameter without an annotation and\or a default value for {$name}. Container is unable to autowire.");
                }

                $resolved = $this->maybeResolveUnionTypeDependency($type, $id, $name);
                if (false !== $resolved) {
                    return $resolved;
                }

                $resolved = $this->maybeResolveIntersectionTypeDependency($type, $id);
                if (false !== $resolved) {
                    return $resolved;
                }

                $resolved = $this->maybeResolveNamedTypeDependency($type, $id, $name);
                if (false !== $resolved) {
                    return $resolved;
                }

                throw new AutowiringFailureException($id, "Unable to resolve dependencies for {$id}. Parameter {$name} uses an unknown type.");
            } catch(AutowiringFailureException $e) {
                if ($param->isDefaultValueAvailable()) {
                    return $param->getDefaultValue();
                }

                // if($param->allowsNull()) {
                //     return null;
                // }

                throw new AutowiringFailureException($id, previous: $e);
            }
        }, $parameters);
    }

    protected function maybeResolveDependencyFromConfig(?ReflectionType $type) : mixed {
        if (!$this->is_loading_configurations || !($type instanceof ReflectionNamedType)) {
            return false;
        }

        return $this->configurator->resolveDependencyFromConfigs($type->getName());
    }

    protected function maybeResolveDependencyFromAnnotation(string $name, string|false $doc_comment, string $id) : mixed {
        if (false === $doc_comment) {
            return false;
        }

        $match = [];
        \Safe\preg_match("/(?<=@param).*(?=\\\${$name})/m", $doc_comment, $match);

        if (empty($match)) {
            return false;
        }

        $annotated_types = array_map(fn ($annotated_type) => str_starts_with($annotated_type, '\\') ? substr($annotated_type, 1) : $annotated_type, explode('|', trim($match[0])));

        foreach ($annotated_types as $annotated_type) {
            if (in_array($annotated_type, self::UNRESOLVABLE_TYPES)) {
                continue;
            }

            return $this->tryGetting($annotated_type, $id);
        }

        return false;
    }

    protected function maybeResolveUnionTypeDependency(ReflectionType $type, string $id, string $name) : mixed {
        if (!($type instanceof ReflectionUnionType)) {
            return false;
        }

        foreach ($type->getTypes() as $union_type) {
            if ($union_type instanceof ReflectionNamedType) {
                if (in_array($union_type->getName(), self::UNRESOLVABLE_TYPES)) {
                    continue;
                }

                return $this->tryGetting($union_type->getName(), $id);
            }

            if ($union_type instanceof ReflectionIntersectionType) {
                return $this->maybeResolveIntersectionTypeDependency($union_type, $id);
            }
        }

        throw new AutowiringFailureException($id, "{$id} uses a union type parameter for {$name}, but container couldn't resolve any of the types. You may want to bind some of those types manually.");
    }

    protected function maybeResolveNamedTypeDependency(ReflectionType $type, string $id, string $name) : mixed {
        if (!($type instanceof ReflectionNamedType)) {
            return false;
        }

        if (!$type->isBuiltin() && !in_array($type->getName(), self::UNRESOLVABLE_TYPES)) {
            return $this->tryGetting($type->getName(), $id);
        }

        throw new AutowiringFailureException($id, "{$id} uses a built-in parameter with no default value for {$name}. Container is unable to autowire.");
    }

    protected function maybeResolveIntersectionTypeDependency(ReflectionType $type, string $id) : mixed {
        if (!($type instanceof ReflectionIntersectionType)) {
            return false;
        }

        /** @var class-string[] $named_types */
        $named_types = array_map(function ($named_type) {
            if ($named_type instanceof ReflectionNamedType) {
                return $named_type->getName();
            }
        }, $type->getTypes());

        return $this->tryGetting($named_types, $id);
    }
}
