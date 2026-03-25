<?php
namespace Juzdy\Container\Pipeline\Pipe;

use Juzdy\Container\Context\ContextInterface;

class LifeCycler extends AbstractPipelineablePipe
{

    /**
     * {@inheritDoc}
     * 
     * Executes lifecycle-related plugins to manage the lifecycle of the service instance, such as sharing and cloning.
     */

    public function __invoke(ContextInterface $context, callable $next): mixed
    {
        $this->getPipeline()->process($context);

        return $next($context);
    }
    
}
