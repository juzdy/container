<?php
namespace Juzdy\Container\Plugin\LifeCycle;

use Juzdy\Container\Contract\Lifecycle\PrototypeInterface;
use Juzdy\Container\Plugin\PluginInterface;

class Prototype implements PluginInterface
{

    /**
     * {@inheritDoc}
     *
     * Resolves parameter preferences defined via Using attribute on the target parameter.
     */
    public function __invoke(mixed $context, callable $next): mixed
    {
        if (in_array(PrototypeInterface::class, class_implements($context->class()), true)) {
            $prototype = clone $context->instance();
            $context->instance($prototype);
        }

        return $next($context);
    }

}