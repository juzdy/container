<?php

namespace Juzdy\Container\Pipeline\Pipe\DependencyCollector;

use ReflectionParameter;
use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Exception\RuntimeException;
use Juzdy\Container\Pipeline\Pipe\DependencyCollector\AbstractDependencyCollectorPipe;
use Psr\Container\NotFoundExceptionInterface;

class UseTypeHint extends AbstractDependencyCollectorPipe
{

    public function __invoke(ContextInterface $context, callable $next): mixed
    {
       $param = $context->resolvingDependency();
       
        if (!$param instanceof ReflectionParameter) {
            return $next($context);
        }

       $type = $this->paramType($param);

       //todo handle union and intersection types in future

        if ($type->isBuiltin()) {

            if ($param->isDefaultValueAvailable()) {
                $context->depends($param->getDefaultValue());
                return $context;
            }

            throw new RuntimeException (
                sprintf(
                    "Cannot resolve built-in type parameter '%s' in class '%s'.",
                    $param->getName(),
                    $param->getDeclaringClass()->getName(),
                )
            );
        }

        $typeName = $type->getName();
        $id = $typeName;

        
        try {
            $service = $context->container()->get($id);
            $context->depends($service);

            return $context;

        } catch (NotFoundExceptionInterface) {
            // Service not found, continue to next plugin
        }
        

        return $next($context);
    }
}
