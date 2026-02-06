<?php
namespace Juzdy\Container\Plugin\Resolver;

use Juzdy\Container\Plugin\PluginInterface;

class WireResolver implements PluginInterface
{

    /**
     * {@inheritDoc}
     *
     * Resolves parameter preferences defined via Using attribute on the target parameter.
     */
    public function __invoke(mixed $target, callable $next): mixed
    {
        // @todo Implement configuration-based parameter resolution
        return $next($target);
    }
}