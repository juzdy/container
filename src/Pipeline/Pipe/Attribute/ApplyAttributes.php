<?php
namespace Juzdy\Container\Pipeline\Pipe\Attribute;

use Juzdy\Container\Attribute\AttributeApplicableInterface;
use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Pipeline\ContextPipeInterface;

class ApplyAttributes implements ContextPipeInterface
{
    public function __invoke(ContextInterface $context, callable $next): mixed
    {
        $reflection = $context->reflection();
        $instance = $context->instance();

        foreach ($reflection->getAttributes() as $attribute) {
            $attributeInstance = $attribute->newInstance();
            if ($attributeInstance instanceof AttributeApplicableInterface) {
                $attributeInstance->apply($instance/*, $context*/);
            }
        }

        return $next($context);
    }
}