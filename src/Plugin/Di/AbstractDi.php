<?php
namespace Juzdy\Container\Plugin\Di;

use Juzdy\Container\Exception\RuntimeException;
use Juzdy\Container\Plugin\PluginInterface;
use ReflectionNamedType;
use ReflectionParameter;

abstract class AbstractDi implements PluginInterface
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