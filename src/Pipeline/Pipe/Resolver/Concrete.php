<?php

namespace Juzdy\Container\Pipeline\Pipe\Resolver;

use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Pipeline\ContextPipeInterface;

class Concrete implements ContextPipeInterface
{

    public function __invoke(ContextInterface $context, callable $next): ContextInterface
    {
        $class = $context->id();
        
        if (class_exists($class)) {
            
            $context->class($class);

            if ($context->isInstantiable()) {
                return $context;
            }
        }

        return $next($context);
    }
}
