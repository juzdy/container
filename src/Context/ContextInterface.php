<?php
namespace Juzdy\Container\Context;

use Juzdy\Container\JuzdyContainerInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Traversable;

interface ContextInterface
{
    public const PROPERTY_CURRENT_PARAMETER = 'current_parameter';

    /**
     * Get or set the fully qualified class name for the current context.
     *
     * When no class name is provided, implementation may infer it from a resolved instance.
     *
     * @param string|null $className Class name to assign to context state.
     *
     * @return string|null Currently resolved class name.
     */
    public function class(?string $className = null): ?string;

    /**
     * Retrieve the container instance or resolve a service directly from it.
     *
     * @param string|null $id Service identifier to resolve from the container.
     *
     * @return JuzdyContainerInterface|mixed Container instance when $id is null, otherwise resolved service.
     */
    public function container(?string $id = null): mixed;

    /**
     * Get reflection information for the current class constructor.
     *
     * @return ReflectionMethod|null Constructor reflection if class is resolvable, otherwise null.
     */
    public function constructor(): ?ReflectionMethod;

    /**
     * Register dependencies discovered for the current context.
     *
     * @param mixed ...$dependencies Dependencies to append to the internal dependency list.
     *
     * @return array<int, mixed> Full dependency list after append operation.
     */
    public function depends(...$dependencies): array;

    /**
     * Get or set the identifier being resolved by the context.
     *
     * @param string|null $id Service identifier to assign.
     *
     * @return string|static Current identifier when reading, or fluent context instance when writing.
     */
    public function id(?string $id = null): string|static;

    /**
     * Determine whether an instance was already created for this context.
     *
     * @return bool True when instance is available.
     */
    public function isInstantiated(): bool;

    /**
     * Determine whether no instance is available for this context.
     *
     * @return bool True when instance is not available.
     */
    public function isNotInstantiated(): bool;

    /**
     * Determine whether the current class can be instantiated.
     *
     * @return bool True when reflection exists and class is instantiable.
     */
    public function isInstantiable(): bool;

    /**
     * Determine whether the current class cannot be instantiated.
     *
     * @return bool True when class is not instantiable.
     */
    public function isNotInstantiable(): bool;

    /**
     * Determine whether the context was resolved to a class.
     *
     * @return bool True when class information is available.
     */
    public function isResolved(): bool;

    /**
     * Determine whether the context is still unresolved.
     *
     * @return bool True when class information is not available.
     */
    public function isNotResolved(): bool;

    /**
     * Get or set the concrete instance associated with the context.
     *
     * @param mixed $instance Instance to assign.
     *
     * @return mixed Stored instance when reading, or fluent context instance when writing.
     */
    public function instance(mixed $instance = null): mixed;

    /**
     * Yield constructor parameters for dependency resolution.
     *
     * @return Traversable<ReflectionParameter> Constructor parameter iterator.
     */
    public function params(): Traversable;

    /**
     * Get or set arbitrary context property value.
     *
     * @param string $name Property key.
     * @param mixed $value Property value to set.
     *
     * @return mixed Property value when reading, or fluent context instance when writing.
     */
    public function property(string $name, mixed $value = null): mixed;

    /**
     * Get reflection metadata for the current class.
     *
     * @return ReflectionClass|null Class reflection for resolved class, otherwise null.
     */
    public function reflection(): ?ReflectionClass;

    /**
     * Get or set currently resolving constructor parameter.
     *
     * Pass `false` to clear currently tracked resolving parameter.
     *
     * @param ReflectionParameter|false|null $param Resolving parameter to assign, or false to clear.
     *
     * @return ReflectionParameter|static|null Current parameter when reading, or fluent context instance when writing.
     */
    public function resolvingDependency(ReflectionParameter|false|null $param = null): ReflectionParameter|static|null;

    /**
     * Determine whether resolved service should be treated as shared singleton.
     *
     * @return bool True when class is marked shared by contract or attribute.
     */
    public function shouldShare(): bool;

    /**
     * Determine whether resolved service should be treated as prototype (shared) so will be cloned from fresh instance.
     *
     * @return bool True when class is marked prototype by contract or attribute.
     */
    public function shouldPrototype(): bool;

}