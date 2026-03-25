<?php
namespace Juzdy\Container\Pipeline\Pipe\Instantiator;

use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Contract\Factory\LazyGhostInterface;
use Juzdy\Container\Pipeline\ContextPipeInterface;

class LazyGhostInstance implements ContextPipeInterface
{

    /**
     * {@inheritDoc}
     * 
     * Instantiates classes implementing LazyGhostInterface as lazy ghost proxies
     * using the provided dependencies when the proxy is initialized.
     */
    public function __invoke(ContextInterface $context, callable $next): mixed
    {
        $constructor = $context->constructor();

        $hasAccessableConstructor =
            $constructor &&
            $constructor->isPublic();

        if (
            is_a($context->class(), LazyGhostInterface::class, true)
            && $hasAccessableConstructor
        ) {
            
            $dependencies = $context->depends();

            $service = $context->reflection()->newLazyGhost(
                static function ($object) use ($dependencies) {
                    $object->__construct(...$dependencies);
                }
            );
            $context->instance($service);

            return $context;
            
        }

        return $next($context);
    }
}