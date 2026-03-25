<?php
namespace Juzdy\Container\Pipeline\Pipe\Fetcher;

use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Pipeline\ContextPipeInterface;

class Prototype implements ContextPipeInterface
{

    
    public function __invoke(ContextInterface $context, callable $next): mixed
    {
        if ($context->isInstantiated() && $context->shouldPrototype()) {
            die("Prototyping Instance for '{$context->class()}'\n");
            $context->instance(
                clone $context->instance()
            );

        }

        return $next($context);
    }

}