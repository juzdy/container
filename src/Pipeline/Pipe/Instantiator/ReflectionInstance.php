<?php
namespace Juzdy\Container\Pipeline\Pipe\Instantiator;

use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Pipeline\ContextPipeInterface;

class ReflectionInstance implements ContextPipeInterface
{

    /**
     * {@inheritDoc}
     * 
     * Instantiates the service using its standard constructor with the provided dependencies.
     */
    public function __invoke(ContextInterface $context, callable $next): mixed
    {
        $service = $context->reflection()->newInstanceArgs(...$context->depends());

        $context->instance($service);

        return $context;
    }
}