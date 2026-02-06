<?php

namespace Juzdy\Container\Plugin\Shared;

use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Exception\FinalNotFoundException;
use Juzdy\Container\Plugin\PluginInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionParameter;

class TypeResolver implements PluginInterface
{

    public function __invoke(mixed $target, callable $next): mixed
    {
        /** @var ContextInterface $context */
        /** @var \ReflectionParameter $param */
        $context = $target;
        $param = $context->attribute(ContextInterface::ATTRIBUTE_CURRENT_PARAMETER);

       $type = $param->getType();

        if ($type === null) {
            throw new FinalNotFoundException ("Cannot resolve parameter '{$param->getName()}'.");
        }

        if ($type->isBuiltin()) {
            if ($param->isDefaultValueAvailable()) {
                return $param->getDefaultValue();
            }
            throw new FinalNotFoundException ("Cannot resolve built-in type parameter '{$param->getName()}'.");
        }

        $typeName = $type->getName();
        $preference = $typeName;

        try {
            $service = $context->getContainer()->get($preference);
            
            return $service;
        } catch (NotFoundExceptionInterface) {
            // Service not found, continue to next plugin
        }

        return $next($target);
    }
}
