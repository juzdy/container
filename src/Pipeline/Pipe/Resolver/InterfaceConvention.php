<?php
namespace Juzdy\Container\Pipeline\Pipe\Resolver;

use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Pipeline\ContextPipeInterface;

class InterfaceConvention implements ContextPipeInterface
{

    public function __invoke(ContextInterface $context, callable $next): ContextInterface
    {
        $class = $context->id();
        
        if (str_ends_with($class, 'Interface') && interface_exists($class)) {
            // Auto-resolve interface to class by removing 'Interface' suffix
            // Simple convention-based resolution
            $preference = preg_replace('/Interface$/', '', $class);
            if (class_exists($preference)) {
                $context->class($preference);
                return $context;
            }
        }

        return $next($context);
    }
}
