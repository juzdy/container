<?php
namespace Juzdy\Container\Plugin\LifeCycle;

use Juzdy\Container\Attribute\Shared as AttributeShared;
use Juzdy\Container\Contract\Lifecycle\SharedInterface;
use Juzdy\Container\Plugin\PluginInterface;

class Shared implements PluginInterface
{

    /**
     * {@inheritDoc}
     *
     * Resolves parameter preferences defined via Using attribute on the target parameter.
     */
    public function __invoke(mixed $context, callable $next): mixed
    {
        if ($this->isShared($context)) {
            $context->container()
                ->share(
                    $context->id(),
                    $context->instance()
                );
        }

        return $next($context);
    }

    /**
     * Determines if the service should be treated as shared based on its implementation
     * of SharedInterface or the presence of the Shared attribute.
     * 
     * @param mixed $context
     * 
     * @return bool
     */
    protected function isShared(mixed $context): bool
    {
        // Check if the class implements SharedInterface
        if (in_array(SharedInterface::class, class_implements($context->class()), true)) {
            return true;
        }
        // Or
        // Check for the Shared attribute
        $sharedAttributes = $context->reflection()
            ->getAttributes(AttributeShared::class);

        if (count($sharedAttributes) > 0) {
            /** @var AttributeShared $sharedAttribute */
            $sharedAttribute = $sharedAttributes[0]->newInstance();

            return $sharedAttribute->isShared();
        }

        return false;
    }

}