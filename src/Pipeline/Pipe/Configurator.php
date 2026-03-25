<?php
namespace Juzdy\Container\Pipeline\Pipe;

use Juzdy\Container\Context\ContextInterface;

class Configurator extends AbstractPipelineablePipe
{


    public function __invoke(ContextInterface $context, callable $next): mixed
    {
        if ($context->isInstantiated()) {   
            $context = $this->getPipeline()->process($context);
        }

        return $next($context);
    }
}
