<?php
namespace Juzdy\Container\Plugin\Factory;

use Juzdy\Container\Exception\RuntimeException;
use Juzdy\Container\Plugin\PluginInterface;

class StandardFactory implements PluginInterface
{

    /**
     * {@inheritDoc}
     * 
     * Instantiates the service using its standard constructor with the provided dependencies.
     */
    public function __invoke(mixed $context, callable $next): mixed
    {
        try {
            return new ($context->class())(...$context->depends());
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'Failed to instantiate service: ' . $context->class() . '. Reason: ' . $e->getMessage(),
                0,
                $e,
            );
        }

        return $next($context);
    }
}