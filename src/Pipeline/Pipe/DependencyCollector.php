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
            //$context->property(ContextInterface::PROPERTY_CURRENT_PARAMETER, $param);
            $context->resolvingDependency($param);

            $context = $this->getPipeline()->process($context);

            //$context->depends($dependency);
        }

        

        return $next($context);
    }
}