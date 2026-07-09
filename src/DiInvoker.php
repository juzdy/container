<?php
namespace Juzdy\Container;

use Psr\Container\ContainerInterface;
use Juzdy\Container\Attribute\Shared;
use Juzdy\Container\Exception\DiInvoker\DiInvokerException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

#[Shared]
class DiInvoker
{
    public function __construct(
        private ContainerInterface $container
    ) {
    }

    public function __invoke(callable $callable): mixed
    {
        return $this->invoke($callable);
    }

    public function invoke(callable $callable): mixed
    {
        [$reflection, $resolvedCallable] = $this->resolveCallable($callable);

        $deps = $this->resolveDependencies($reflection->getParameters());

        return $resolvedCallable(...$deps);
    }

    protected function resolveCallable(callable $callable): array
    {
        if ($callable instanceof \Closure) {
            return [new ReflectionFunction($callable), $callable];
        }

        if (is_string($callable)) {
            if (str_contains($callable, '::')) {
                [$className, $methodName] = explode('::', $callable, 2);
                return $this->resolveClassMethodCallable($className, $methodName);
            }

            if (class_exists($callable)) {
                return $this->resolveInvokableClassCallable($callable);
            }

            if (function_exists($callable)) {
                return [new ReflectionFunction($callable), $callable];
            }

            throw new DiInvokerException("Function or class callable '{$callable}' does not exist.");
        }

        if (is_array($callable)) {
            $target = $callable[0] ?? null;
            $methodName = $callable[1] ?? '__invoke';

            if (is_object($target)) {
                if (!method_exists($target, $methodName)) {
                    $className = $target::class;
                    throw new DiInvokerException("Method {$methodName} does not exist in class {$className}.");
                }

                return [new ReflectionMethod($target, $methodName), [$target, $methodName]];
            }

            if (is_string($target)) {
                return $this->resolveClassMethodCallable($target, $methodName);
            }

            throw new DiInvokerException("Invalid array callable provided.");
        }

        if (is_object($callable) && method_exists($callable, '__invoke')) {
            return [new ReflectionMethod($callable, '__invoke'), [$callable, '__invoke']];
        }

        throw new DiInvokerException("Invalid callable provided.");
    }

    protected function resolveClassMethodCallable(string $className, string $methodName): array
    {
        if (!class_exists($className)) {
            throw new DiInvokerException("Class {$className} does not exist.");
        }

        if (!method_exists($className, $methodName)) {
            throw new DiInvokerException("Method {$methodName} does not exist in class {$className}.");
        }

        $reflectionMethod = new ReflectionMethod($className, $methodName);

        if ($reflectionMethod->isStatic()) {
            return [$reflectionMethod, [$className, $methodName]];
        }

        $instance = $this->resolveFromContainer($className, $className, $methodName);

        return [new ReflectionMethod($instance, $methodName), [$instance, $methodName]];
    }

    protected function resolveInvokableClassCallable(string $className): array
    {
        if (!method_exists($className, '__invoke')) {
            throw new DiInvokerException("Class {$className} is not invokable.");
        }

        $reflectionMethod = new ReflectionMethod($className, '__invoke');

        if ($reflectionMethod->isStatic()) {
            return [$reflectionMethod, [$className, '__invoke']];
        }

        $instance = $this->resolveFromContainer($className, $className, '__invoke');

        return [new ReflectionMethod($instance, '__invoke'), [$instance, '__invoke']];
    }

    protected function resolveDependencies(array $parameters): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            if ($parameter->isVariadic()) {
                continue;
            }

            $type = $parameter->getType();

            if (!$type instanceof ReflectionNamedType) {
                if ($this->canUseDefault($parameter)) {
                    continue;
                }

                throw new DiInvokerException("Cannot resolve parameter \${$parameter->getName()} with unsupported type.");
            }

            if ($type->isBuiltin()) {
                if ($this->canUseDefault($parameter)) {
                    continue;
                }

                throw new DiInvokerException("Cannot resolve built-in type for parameter \${$parameter->getName()}.");
            }

            $typeName = $type->getName();

            if ($type->allowsNull()) {
                $dependencies[] = null;
                continue;
            }

            if ($this->canUseDefault($parameter)) {
                continue;
            }

            try {
                $dependencies[] = $this->getContainer()->get($typeName);
            } catch (\Psr\Container\NotFoundExceptionInterface $e) {

                throw new DiInvokerException(
                    "Cannot resolve dependency: {$typeName} for parameter \${$parameter->getName()}.",
                    0,
                    $e
                );
            }
        }

        return $dependencies;
    }

    protected function resolveFromContainer(string $serviceName, string $className, string $methodName): object
    {
        try {
            return $this->getContainer()->get($serviceName);
        } catch (\Psr\Container\NotFoundExceptionInterface $e) {
            throw new DiInvokerException("Cannot resolve class {$className} for callable {$className}::{$methodName}.", 0, $e);
        }
    }

    protected function canUseDefault(ReflectionParameter $parameter): bool
    {
        return $parameter->isOptional() || $parameter->isDefaultValueAvailable();
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}