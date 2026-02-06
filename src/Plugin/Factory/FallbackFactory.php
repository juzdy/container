<?php

namespace Juzdy\Container\Plugin\Factory;

use Juzdy\Container\Exception\RuntimeException;
use Juzdy\Container\Plugin\PluginInterface;

/**
 * Fallback factory plugin
 *
 * @package Juzdy\Container\Plugin\Factory
 */
class FallbackFactory implements PluginInterface
{
    /**
     * {@inheritDoc}
     *
     * Throws an exception indicating that no factory was found for the requested service.
     * Used as a last resort in the factory plugin chain.
     */
    public function __invoke(mixed $context, callable $next): mixed
    {
        throw new RuntimeException('No factory found for the requested service: ' . $context->class());
    }
}