<?php

namespace Juzdy\Container\Plugin\Di;

use ReflectionParameter;
use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Exception\RuntimeException;
use Psr\Container\NotFoundExceptionInterface;

class UseType extends AbstractDi
{

    public function __invoke(mixed $target, callable $next): mixed
    {
        /** @var ContextInterface $context */
        /** @var \ReflectionParameter $param */
        $context = $target;
        $param = $context->attribute(ContextInterface::ATTRIBUTE_CURRENT_PARAMETER);

       $type = $this->paramType($param);

       //todo handle union and intersection types in future

        if ($type->isBuiltin()) {

            if ($param->isDefaultValueAvailable()) {
                return $param->getDefaultValue();
            }

            throw new RuntimeException (
                sprintf(
                    "Cannot resolve built-in type parameter '%s' in class '%s'.",
                    $param->getName(),
                    $context->class(),
                )
            );
        }

        $typeName = $type->getName();
        $id = $typeName;

        try {

            $service = $context->container()->get($id);
            
            return $service;
        } catch (NotFoundExceptionInterface) {
            // Service not found, continue to next plugin
        }

        return $next($target);
    }
}
