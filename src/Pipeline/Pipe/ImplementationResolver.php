<?php
namespace Juzdy\Container\Pipeline\Pipe;

use Juzdy\Container\Context\ContextInterface;

use Juzdy\Container\Exception\ClassNotFoundException;
use Juzdy\Container\Exception\ClassNotResolvedException;

class ImplementationResolver extends AbstractPipelineablePipe
{

    public function __invoke(ContextInterface $context, callable $next): ContextInterface
    {

        if ($context->isNotResolved()) {
        
            $context = $this->getPipeline()->process($context);

            if ($context->isNotResolved()) {
                throw new ClassNotResolvedException(
                    "Cannot resolve service '{$context->id()}'.",
                    [
                        'service' => $context->id(),
                    ]
                );
            }

            if (!class_exists($context->class())) {
                throw new ClassNotFoundException(
                    "Resolved class '{$context->class()}' for service '{$context->id()}' does not exist.",
                    [
                        'service' => $context->id(),
                        'class' => $context->class(),
                    ]
                );
            }

            
        }

        return $next($context);
    }
}