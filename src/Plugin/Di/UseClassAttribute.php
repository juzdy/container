<?php
namespace Juzdy\Container\Plugin\Di;

use Juzdy\Container\Attribute\Preference;
use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Exception\RuntimeException;
use ReflectionParameter;

/**
 * Class preference attribute resolver plugin
 *
 * Resolves class preferences defined via \Juzdy\Container\Attribute\Preference attribute on the target class.
 *
 * @package Juzdy\Container\Plugin\Resolver
 */
class UseClassAttribute extends AbstractDi
{

    /**
     * {@inheritDoc}
     * 
     * Resolves class preferences defined via Preference attribute on the target class.
     * @see Preference
     */
    public function __invoke(mixed $target, callable $next): mixed
    {
        /** @var ContextInterface $context */
        $context = $target;
        /** @var \ReflectionParameter $param */
        $param = $context->attribute(ContextInterface::ATTRIBUTE_CURRENT_PARAMETER);


        $typeName = $this->paramType($param)->getName();

       $preferenceAttributes = $context->reflection()->getAttributes(Preference::class);
        if (count($preferenceAttributes) > 0) {
            $attribute = $preferenceAttributes[0];
            $preferenceInstance = $attribute->newInstance();
            $preference = $preferenceInstance->getPreference($typeName);
            if ($preference !== null) {
                if (!is_a($preference, $typeName, true)) {
                    throw new RuntimeException("Preference '{$preference}' is not a valid implementation of '{$typeName}'.");
                }

                return $context->container()->get($preference);
            }
        }

        return $next($target);
    }
}