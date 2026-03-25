<?php
namespace Juzdy\Container\Pipeline\Pipe;

use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Pipeline\ContextPipeInterface;
use Juzdy\Container\Plugin\Factory\FallbackFactory;
use Juzdy\Container\Plugin\Factory\LazyGhostFactory;
use Juzdy\Container\Plugin\Factory\StandardFactory;
use Juzdy\Container\PluginManager;
use Juzdy\Container\PluginManagerInterface;
use Throwable;

class Instantiator extends AbstractPipelineablePipe
{


    public function __invoke(ContextInterface $context, callable $next): mixed
    {
        if ($context->isNotInstantiated()) {

            $context = $this->getPipeline()->process($context);
            
            if ($context->isNotInstantiated()) {
                throw new \RuntimeException("Instantiator failed to create an instance for service '{$context->id()}' with resolved class '{$context->class()}'.");
            }
        }

        return $next($context);
    }
}
