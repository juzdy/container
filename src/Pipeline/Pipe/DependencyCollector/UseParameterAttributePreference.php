<?php
namespace Juzdy\Container\Pipeline\Pipe\DependencyCollector;

use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Attribute\Parameter\Using;
use Juzdy\Container\Exception\RuntimeException;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionParameter;


class UseParameterAttributePreference extends AbstractDependencyCollectorPipe
{
    /**
     * {@inheritDoc}
     *
     * Resolves parameter preferences defined via Juzdy\Container\Attribute\Parameter\Using attribute on the target parameter.
     */
    public function __invoke(ContextInterface $context, callable $next): ContextInterface
    {
        $param = $context->resolvingDependency();

        if (!$param instanceof ReflectionParameter) {
            return $next($context);
        }

        $attributes = $param->getAttributes(Using::class);

        if (count($attributes) > 0) {

            /** @var \ReflectionAttribute $attribute */
            $attribute = $attributes[0];

            /** @var Using $parameterInstance */
            $parameterInstance = $attribute->newInstance();

            /** @var string|null $preference */
            $preference = $parameterInstance->getPreference();

            if ($preference !== null) {
                $type = $this->paramType($param);
                if (!is_a($preference, $type->getName(), true)) {
                    throw new RuntimeException("Preference '{$preference}' is not a valid implementation of '{$type->getName()}'.");
                }
                
                try {
                    $service = $context->container()->get($preference);
                    $context->depends($service);

                    return $context;
                } catch (NotFoundExceptionInterface) {
                    // Service not found, continue to next plugin
                }
            }
        }

        return $next($context);
    }
}