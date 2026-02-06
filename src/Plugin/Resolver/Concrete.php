<?php

namespace Juzdy\Container\Plugin\Resolver;

use Juzdy\Container\Plugin\PluginInterface;

class Concrete implements PluginInterface
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
        if (class_exists($class)) {
            $context->class($class);
            return true;
        }

        return $next($target);
    }
}
