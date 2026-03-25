<?php
namespace Juzdy\Container\Pipeline\Pipe\Configurator;

use Juzdy\Container\Attribute\Method\Injector as MethodInjector;
use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Contract\InjectableInterface;
use Juzdy\Container\Exception\NotFoundException;
use Juzdy\Container\Exception\RuntimeException;
use Juzdy\Container\Pipeline\ContextPipeInterface;
use ReflectionMethod;

class Injector implements ContextPipeInterface
{

    /**
     * {@inheritDoc}
     */
    public function __invoke(ContextInterface $context, callable $next): mixed
    {
        if ($context->instance() instanceof InjectableInterface) {
            $reflection = $context->reflection();
            $instance = $context->instance();

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $attributes = $method->getAttributes(MethodInjector::class);
                if (count($attributes) === 0) {
                    continue;
                }

                $parameters = [];
                foreach ($method->getParameters() as $parameter) {
                    $injection = $parameter->getType()?->getName();
                    if ($injection === null) {
                        throw new RuntimeException(
                            sprintf(
                                'Cannot inject parameter %s in method %s of class %s: missing type hint',
                                $parameter->getName(),
                                $method->getName(),
                                $context->class(),
                            )
                        );
                    }
                    try {
                        $context->container()->get($injection);
                    } catch (NotFoundException $ex) {
                        throw new RuntimeException(
                            sprintf(
                                'Cannot inject parameter %s in method %s of class %s: service %s not found in container',
                                $parameter->getName(),
                                $method->getName(),
                                $context->class(),
                                $injection,
                            )
                        );
                    }
                    $parameters[] = $context->container()->get($injection);
                }

                $method->invoke($instance, ...$parameters);
            }
        }
    
        
        return $next($context);
    }
    
}