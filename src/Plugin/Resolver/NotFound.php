<?php

namespace Juzdy\Container\Plugin\Resolver;

use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Exception\NotFoundException;
use Juzdy\Container\Plugin\PluginInterface;
use ReflectionParameter;

class NotFound implements PluginInterface
{

    public function __invoke(mixed $target, callable $next): mixed
    {
        /** @var ContextInterface $context */
        $context = $target;

        throw new NotFoundException($this->formatErrorMessage($context));
    }

    private function formatErrorMessage(ContextInterface $context): string
    {
        return sprintf(
            "Cannot resolve dependency '%s' . Service stack: [%s]",
            $context->id(),
            implode(' -> ', $context->container()->stack())
        );
    }
}
