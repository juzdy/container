<?php
namespace Juzdy\Container\Pipeline\Pipe\LifeCycler;

use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Pipeline\ContextPipeInterface;
use Juzdy\Container\Repository\ShareManager;

class ShareIfApplicable implements ContextPipeInterface
{

    /**
     * {@inheritDoc}
     *
     * Shares the instance in the ShareManager if the context is marked to be shared and is already instantiated.
     */
    public function __invoke(ContextInterface $context, callable $next): mixed
    {
        if ($context->isInstantiated() && $context->shouldShare()) {
            $instance = $context->instance();
            $context->container(ShareManager::class)
                ->share(
                    [$context->id(), $context->class()],
                    $instance
                );
        }

        return $next($context);
    }

}