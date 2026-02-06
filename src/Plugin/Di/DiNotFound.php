<?php

namespace Juzdy\Container\Plugin\Di;

use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Exception\NotFoundException;

class DiNotFound extends AbstractDi
{

    /**
     * {@inheritDoc}
     */
    public function __invoke(mixed $target, callable $next): mixed
    {
        /** @var ContextInterface $context */
        $context = $target;

        throw new NotFoundException($this->formatErrorMessage($context));
    }

    /**
     * Formats the error message for a not found exception.
     * 
     * @param ContextInterface $context
     * 
     * @return string
     */
    private function formatErrorMessage(ContextInterface $context): string
    {
        return sprintf(
            "Cannot resolve service '%s' . Service stack: [%s]",
            $context->id(),
            implode(' -> ', $context->container()->stack())
        );
    }
}
