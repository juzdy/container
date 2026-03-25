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
            $context->container(ShareManager::class)
                ->share(
                    $context->class(),
                    $context->instance()
                );
        }

        return $next($context);
    }

}