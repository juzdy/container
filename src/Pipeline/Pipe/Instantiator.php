<?php
namespace Juzdy\Container\Pipeline\Pipe;

use Juzdy\Container\Context\ContextInterface;

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
