<?php
namespace Juzdy\Container\Plugin\Di;

use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Attribute\Parameter\Using;
use Juzdy\Container\Exception\RuntimeException;
use ReflectionParameter;


class UseParameterAttribute extends AbstractDi
{
    /**
     * {@inheritDoc}
     *
     * Resolves parameter preferences defined via Juzdy\Container\Attribute\Parameter\Using attribute on the target parameter.
     */
    public function __invoke(mixed $target, callable $next): mixed
    {
        /** @var ContextInterface $context */
        $context = $target;
        /** @var \ReflectionParameter $param */
        $param = $context->property(ContextInterface::PROPERTY_CURRENT_PARAMETER);

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
                
                return $context->container()->get($preference);
            }
        }

        return $next($target);
    }
}