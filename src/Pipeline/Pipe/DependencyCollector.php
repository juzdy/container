<?php
namespace Juzdy\Container\Pipeline\Pipe;

use Juzdy\Container\Context\ContextInterface;

class DependencyCollector extends AbstractPipelineablePipe
{

    public function __invoke(ContextInterface $context, callable $next): ContextInterface
    {
        if ($context->isInstantiated()) {
            return $next($context);
        }

        foreach ($context->params() as $param) {
            
            $context->resolvingDependency($param);

            $context = $this->getPipeline()->process($context);
        }

        return $next($context);
    }
}