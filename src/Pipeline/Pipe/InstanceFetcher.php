<?php
namespace Juzdy\Container\Pipeline\Pipe;

use Juzdy\Container\Context\ContextInterface;

class InstanceFetcher extends AbstractPipelineablePipe
{

    /**
     * {@inheritDoc}
     * 
     * Executes fetch-related plugins to manage the fetching of the service instance, such as retrieving shared instances.
     */

    public function __invoke(ContextInterface $context, callable $next): mixed
    {
        $this->getPipeline()->process($context);

        return $next($context);
    }
    
}
