<?php
namespace Juzdy\Container\Pipeline\Pipe\Configurator;

use Juzdy\Container\Attribute\Method\Injector as MethodInjector;
use Juzdy\Container\Attribute\Injectable;
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
        if ($this->isInjectable($context)) {
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

    /**
     * Determines if the given instance is eligible for injection based on its class and attributes.
     *
     * @param ContextInterface $context The context to check for injectability.
     * 
     * @return bool True if the instance is injectable, false otherwise.
     */
    private function isInjectable(ContextInterface $context): bool
    {
        $reflection = $context->reflection();

        if (in_array(InjectableInterface::class, $reflection->getInterfaceNames())) {
            return true;
        }

        $attributes = $reflection->getAttributes(Injectable::class);

        return count($attributes) > 0;
    }
    
}