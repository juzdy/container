<?php
namespace Juzdy\Container\Pipeline\Pipe\Resolver;

use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Pipeline\ContextPipeInterface;
use Juzdy\Container\Binder\BindingManager;

class BindingResolver implements ContextPipeInterface
{

    /**
     * {@inheritDoc}
     *
     * Resolves the class name from the binding manager if a binding exists for the context's ID.
     */
    public function __invoke(ContextInterface $context, callable $next): ContextInterface
    {
        $bindingManager = $context->container(BindingManager::class);
        if ($bindingManager->has($context->id())) {
            $resolved = $bindingManager->get($context->id());
            $context->class($resolved);
            

            return $context;
        }

        return $next($context);
    }
}