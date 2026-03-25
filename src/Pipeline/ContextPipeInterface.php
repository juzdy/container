<?php
namespace Juzdy\Container\Pipeline;

use Juzdy\Container\Context\ContextInterface;

interface ContextPipeInterface
{
    /**
     * Process the context and optionally pass it to the next pipe.
     * @return mixed
     */
    public function __invoke(ContextInterface $context, callable $next): mixed;
}