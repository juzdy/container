<?php

namespace Juzdy\Container\Plugin\Resolver;

use Juzdy\Container\Plugin\PluginInterface;

class InterfaceConvention implements PluginInterface
{

    /**
     * {@inheritDoc}
     *
     * Resolves parameter preferences defined via Using attribute on the target parameter.
     */
    public function __invoke(mixed $target, callable $next): mixed
    {
        /** @var ContextInterface $context */
        $context = $target;
        $class = $context->id();
        
        if (str_ends_with($class, 'Interface') && interface_exists($class)) {
            // Auto-resolve interface to class by removing 'Interface' suffix
            // Simple convention-based resolution
            $preference = preg_replace('/Interface$/', '', $class);
            if (class_exists($preference)) {
                $context->class($preference);
                return true;
            }
        }

        return $next($target);
    }
}
