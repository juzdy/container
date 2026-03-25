<?php
namespace Juzdy\Container\Pipeline\Pipe\DependencyCollector;

use Juzdy\Container\Exception\RuntimeException;
use Juzdy\Container\Pipeline\ContextPipeInterface;
use ReflectionNamedType;
use ReflectionParameter;

abstract class AbstractDependencyCollectorPipe implements ContextPipeInterface
{

    /**
     * Asserts that the given parameter has a named type and returns it.
     *
     * @throws RuntimeException if the parameter does not have a named type
     */
    protected function paramType(ReflectionParameter $param): ReflectionNamedType
    {
        $type = $param->getType();

        if (!$type instanceof ReflectionNamedType) {
            throw new RuntimeException (
                sprintf(
                    "Parameter '%s' in class '%s' does not have a type hint or is not a single named type.",
                    $param->getName(),
                    $param->getDeclaringClass()->getName(),
                ),
            );
        }

        return $type;
    }

}