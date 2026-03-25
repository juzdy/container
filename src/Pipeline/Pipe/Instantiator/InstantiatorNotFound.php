<?php

namespace Juzdy\Container\Pipeline\Pipe\Instantiator;

use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Exception\RuntimeException;
use Juzdy\Container\Pipeline\ContextPipeInterface;

class InstantiatorNotFound implements ContextPipeInterface
{
    /**
     * {@inheritDoc}
     *
     * Throws an exception indicating that no instantiator was found for the requested service.
     * Used as a last resort in the instantiator plugin chain.
     */
    public function __invoke(ContextInterface $context, callable $next): mixed
    {
        throw new RuntimeException(
            sprintf(
                "Cannot instantiate service '%s' with resolved class '%s'. No suitable instantiator found.",
                $context->id(),
                $context->class()
            )
        );
    }
}