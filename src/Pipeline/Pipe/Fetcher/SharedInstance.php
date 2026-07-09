<?php
namespace Juzdy\Container\Pipeline\Pipe\Fetcher;

use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Pipeline\ContextPipeInterface;
use Juzdy\Container\Repository\ShareManager;

class SharedInstance implements ContextPipeInterface
{

    /**
     * {@inheritDoc}
     *
     * Shares the instance in the ShareManager if the context is marked to be shared and is already instantiated.
     */
    public function __invoke(ContextInterface $context, callable $next): mixed
    {
        $shareManager = $context->container(ShareManager::class);
        
        // If the context is marked to be shared and has an instance, share it in the ShareManager
        if ($shareManager->has($context->id())) {
            $context->instance($shareManager->get($context->id()));
        } elseif ($context->class() && $shareManager->has($context->class())) {
            // Additionally, if the context has a class and that class is shared, share the instance in the ShareManager
            $context->instance($shareManager->get($context->class() ?? ''));
        }

        return $next($context);
    }

}