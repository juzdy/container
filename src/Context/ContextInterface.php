<?php
namespace Juzdy\Container\Context;

use Juzdy\Container\JuzdyContainerInterface;
use ReflectionClass;
use ReflectionMethod;

interface ContextInterface
{

    const ATTRIBUTE_CURRENT_PARAMETER = 'current_parameter';

    /**
     * Get the container associated with the context
     *
     * @return JuzdyContainerInterface
     */
    public function container(): JuzdyContainerInterface;

    /**
     * Set or get the service instance associated with the context
     *
     * @param mixed $service   The service instance to set (if null, the service is retrieved)
     *
     * @return mixed           The service instance (if getting) or the current instance (if setting)
     */
    public function instance(mixed $instance = null): mixed;

    /**
     * Set or get an attribute associated with the context
     *
     * @param string $name        The name of the attribute
     * @param mixed|null $value   The value to set for the attribute (if null, the attribute is retrieved)
     *
     * @return mixed              The value of the attribute (if getting) or the current instance (if setting)
     */
    public function attribute(string $name, mixed $value = null): mixed;

    /**
     * Set or get the identifier associated with the context
     * 
     * @return string|static
     */
    public function id(?string $id = null): string|static;

    /**
     * Get the class name associated with the context
     * 
     * @return string|null
     */
    public function class(): ?string;

    /**
     * Get the ReflectionClass instance for the context's class
     * @return ReflectionClass|null
     */
    public function reflection(): ?ReflectionClass;

    /**
     * Get the constructor method of the class
     *
     * @return ReflectionMethod|null
     */
    public function constructor(): ?ReflectionMethod;

    /**
     * Register dependencies for the context and return the list of all dependencies
     *
     * @param mixed ...$dependencies    Variadic list of dependencies to register
     * 
     * @return array                    The list of all registered dependencies
     */
    public function depends(...$dependencies): array;
}