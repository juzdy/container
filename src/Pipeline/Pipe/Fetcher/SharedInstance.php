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
        
        if ($shareManager->has($context->class())) {
            $context->instance($shareManager->get($context->class()));
        }

        return $next($context);
    }

}