<?php
namespace Juzdy\Container\Pipeline\Pipe\DependencyCollector;

use Juzdy\Container\Attribute\Prefer;
use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Exception\InvalidPreferenceException;
use Juzdy\Container\Pipeline\Pipe\DependencyCollector\AbstractDependencyCollectorPipe;
use ReflectionParameter;

/**
 * Class preference attribute resolver plugin
 *
 * Resolves class preferences defined via \Juzdy\Container\Attribute\Prefer attribute on the target class.
 *
 * @package Juzdy\Container\Pipeline\Pipe\DependencyCollector
 */
class UseClassAttributePreference extends AbstractDependencyCollectorPipe
{

    /**
     * {@inheritDoc}
     * 
     * Resolves class preferences defined via Prefer attribute on the target class.
     * @see Prefer
     */
    public function __invoke(ContextInterface $context, callable $next): mixed
    {
        /** @var \ReflectionParameter $param */
        $param = $context->resolvingDependency();


        $typeName = $this->paramType($param)->getName();

       $preferenceAttributes = $context->reflection()->getAttributes(Prefer::class);
        if (count($preferenceAttributes) > 0) {
            $attribute = $preferenceAttributes[0];
            $preferenceInstance = $attribute->newInstance();
            $preference = $preferenceInstance->getPreference($typeName);
            if ($preference !== null) {
                if (!is_a($preference, $typeName, true)) {
                    throw new InvalidPreferenceException(
                        "Preference '{$preference}' is not a valid implementation of '{$typeName}'.",
                        [
                            'preference' => $preference,
                            'type' => $typeName,
                            'service' => $context->id(),
                        ]
                    );
                }

                $service = $context->container()->get($preference);
                $context->depends($service);

                return $context;
            }
        }

        return $next($context);
    }
}