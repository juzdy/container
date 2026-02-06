<?php
namespace Juzdy\Container\Plugin\Factory;

use Juzdy\Container\Contract\Factory\LazyGhostInterface;
use Juzdy\Container\Plugin\PluginInterface;

class LazyGhostFactory implements PluginInterface
{

    /**
     * {@inheritDoc}
     * 
     * Instantiates classes implementing LazyGhostInterface as lazy ghost proxies
     * using the provided dependencies when the proxy is initialized.
     */
    public function __invoke(mixed $context, callable $next): mixed
    {
        $reflection = $context->reflection();

        $constructor = $reflection->getConstructor();

        $hasAccessableConstructor =
            $constructor &&
            $constructor->isPublic();

        if (
            is_a($context->class(), LazyGhostInterface::class, true)
            && $hasAccessableConstructor
        ) {
            
            
            return $reflection->newLazyGhost(
                static function ($object) use ($context, $hasAccessableConstructor) {
                    if ($hasAccessableConstructor) {
                        $object->__construct(...$context->depends());
                    }
                }
            );
        }

        return $next($context);
    }
}