<?php

namespace Juzdy\Container\Pipeline\Pipe\DependencyCollector;

use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Exception\NotFoundException;
use Juzdy\Container\Pipeline\Pipe\DependencyCollector\AbstractDependencyCollectorPipe;
use ReflectionParameter;

class DependencyNotFound extends AbstractDependencyCollectorPipe
{

    /**
     * {@inheritDoc}
     */
    public function __invoke(ContextInterface $context, callable $next): mixed
    {
        $param = $context->resolvingDependency();
        throw new NotFoundException($this->formatErrorMessage($context, $param));
    }

    /**
     * Formats the error message for a not found exception.
     * 
     * @param ContextInterface $context
     * @param mixed $param The parameter that failed to resolve, if available.
     * @return string
     */
    private function formatErrorMessage(ContextInterface $context, $param): string
    {
        if ($param instanceof ReflectionParameter) {
            return sprintf(
                "Cannot resolve dependency '%s' for parameter '%s' in class '%s'. Service stack: [%s]",
                $param->getType() ? $param->getType()->getName() : 'unknown',
                $param->getName(),
                $param->getDeclaringClass()->getName(),
                implode(' -> ', $context->container()->stack())
            );
        }
        return sprintf(
            "Cannot resolve service '%s' . Service stack: [%s]",
            $context->id(),
            implode(' -> ', $context->container()->stack())
        );
    }
}
