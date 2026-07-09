<?php
namespace Juzdy\Container\Context;

use Juzdy\Container\JuzdyContainerInterface;
use Juzdy\Container\Contract\Lifecycle\SharedInterface;
use Juzdy\Container\Attribute\Shared as AttributeShared;
use Juzdy\Container\Attribute\Prototype as AttributePrototype;
use Juzdy\Container\Contract\Lifecycle\PrototypeInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Traversable;

class Context implements ContextInterface
{
    /**
     * Current service identifier handled by this context.
     *
     * This is typically an alias or class/interface name requested from the container.
     *
     * @var string
     */
    private string $id;

    /**
     * Arguments passed during service retrieval, used for contextual resolution.
     *
     * @var array<int, mixed>
     */
    private array $args = [];


    /**
     * Dependency resolution stack for the current context, used to track nested resolutions
     *
     * @var array<int, string>
     */
    private array $stack = [];

    /**
     * Container instance used by context to resolve dependencies and nested services.
     *
     * @var JuzdyContainerInterface
     */
    private JuzdyContainerInterface $container;

    /**
     * Materialized service instance produced by the resolution pipeline.
     *
     * @var mixed
     */
    protected mixed $instance = null;

    /**
     * Resolved concrete class name for the current context.
     *
     * May be assigned explicitly by pipes or inferred from an already-instantiated object.
     *
     * @var string|null
     */
    protected ?string $className = null;

    /**
     * Cached reflection metadata for the resolved class.
     *
     * @var ReflectionClass|null
     */
    protected ?ReflectionClass $reflectionClass = null;

    /**
     * Cached constructor reflection for the resolved class.
     *
     * @var ReflectionMethod|null
     */
    protected ?ReflectionMethod $constructor = null;

    /**
     * Collected constructor dependencies in their resolved order.
     *
     * @var array<int, mixed>
     */
    protected array $dependencies = [];

    /**
     * Arbitrary context properties shared between pipeline stages.
     *
     * @var array<string, mixed>
     */
    protected array $properties = [];

    /**
     * Cached shared-state decision for current class.
     *
     * Null means value has not been computed yet.
     *
     * @var bool|null
     */
    private ?bool $shouldShare = null;

    /**
     * Cached prototype-state decision for current class.
     *
     * Null means value has not been computed yet.
     *
     * @var bool|null
     */
    private ?bool $shouldPrototype = null;

    /**
     * Parameter currently being resolved inside dependency graph.
     *
     * @var ReflectionParameter|null
     */
    private ?ReflectionParameter $resolvingParameter = null;

    /**
     * @param string $id Service identifier being resolved.
     * @param JuzdyContainerInterface $container Container used for nested resolutions.
     */
    public function __construct(
        string $id,
        JuzdyContainerInterface $container,
        ...$args
        
    )
    {
        $this->id = $id;
        $this->container = $container;
        $this->args = $args;
    }

    /**
     * {@inheritDoc}
     */
    public function container(?string $id = null): mixed
    {
        if ($id !== null) {
            return $this->container->get($id);
        }

        return $this->container;
    }

    /**
     * {@inheritDoc}
     */
    public function id(?string $id = null): string|static
    {
        if ($id !== null) {
            $this->id = $id;
            return $this;
        }

        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function class(?string $className = null): ?string
    {
        if ($className !== null) {
            $this->className = $className;
            $this->reflectionClass = null;
            $this->constructor = null;
            $this->shouldShare = null;
        }

        if ($this->instance() !== null && $this->className === null) {
            $this->className = ($this->instance())::class;
        }

        return $this->className;
    }

    /**
     * {@inheritDoc}
     */
    public function instance(mixed $instance = null): mixed
    {
        if ($instance !== null) {
            $this->instance = $instance;

            return $this;
        }

        return $this->instance;
    }

    /**
     * {@inheritDoc}
     */
    public function stack(?array $stack = null): array|static
    {
        if ($stack !== null) {
            $this->stack = $stack;
            return $this;
        }

        return $this->stack;
    }

    /**
     * {@inheritDoc}
     */
    public function property(string $name, mixed $value = null): mixed
    {
        if ($value !== null) {
            $this->properties[$name] = $value;

            return $this;
        }

        return $this->properties[$name] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function reflection(): ?ReflectionClass
    {
        try {
            $this->reflectionClass ??= new ReflectionClass($this->class());
        } catch (\ReflectionException) {
            return null;
        }

        return $this->reflectionClass;
    }

    /**
     * {@inheritDoc}
     */
    public function constructor(): ?ReflectionMethod
    {
        if ($this->constructor !== null) {
            return $this->constructor;
        }

        return $this->constructor = $this->reflection()?->getConstructor();
    }

    /**
     * {@inheritDoc}
     */
    public function params(): Traversable
    {
        if ($this->constructor() === null) {
            return [];
        }

        foreach ($this->constructor()?->getParameters() ?? [] as $param) {
            yield $param;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function resolvingDependency(ReflectionParameter|false|null $param = null): ReflectionParameter|static|null
    {
        if ($param !== null) {
            if ($param === false) {
                $this->resolvingParameter = null;

                return $this;
            }

            $this->resolvingParameter = $param;

            return $this;
        }

        return $this->resolvingParameter;
    }

    /**
     * {@inheritDoc}
     */
    public function depends(...$dependencies): array
    {
        array_push($this->dependencies, ...$dependencies);

        return $this->dependencies;
    }

    /**
     * {@inheritDoc}
     */
    public function isResolved(): bool
    {
        return $this->class() !== null;
    }

    /**
     * {@inheritDoc}
     */
    public function isNotResolved(): bool
    {
        return !$this->isResolved();
    }

    /**
     * {@inheritDoc}
     */
    public function isInstantiated(): bool
    {
        return $this->instance() !== null;
    }

    /**
     * {@inheritDoc}
     */
    public function isNotInstantiated(): bool
    {
        return !$this->isInstantiated();
    }

    /**
     * {@inheritDoc}
     */
    public function isInstantiable(): bool
    {
        $reflection = $this->reflection();

        return $reflection !== null && $reflection->isInstantiable();
    }

    /**
     * {@inheritDoc}
     */
    public function isNotInstantiable(): bool
    {
        return !$this->isInstantiable();
    }

    /**
     * {@inheritDoc}
     */
    public function shouldShare(): bool
    {
        if ($this->shouldShare !== null) {
            return $this->shouldShare;
        }

        if ($this->class() === null) {
            return $this->shouldShare = false;
        }

        // Check if the class implements SharedInterface
        if (in_array(SharedInterface::class, class_implements($this->class()), true)) {
            return $this->shouldShare = true;
        }

        // Check for the Shared attribute
        $sharedAttributes = $this->reflection()
            ?->getAttributes(AttributeShared::class) ?? [];

        if (count($sharedAttributes) > 0) {
            /** @var AttributeShared $sharedAttribute */
            $sharedAttribute = $sharedAttributes[0]->newInstance();

            return $this->shouldShare = $sharedAttribute->canShare();
        }

        return $this->shouldShare = false;
    }

    /**
     * {@inheritDoc}
     */
    public function shouldPrototype(): bool
    {
        if ($this->shouldPrototype !== null) {
            return $this->shouldPrototype;
        }

        if ($this->class() === null) {
            return $this->shouldPrototype = false;
        }

        // Check if the class implements PrototypeInterface
        if (in_array(PrototypeInterface::class, class_implements($this->class()), true)) {
            return $this->shouldPrototype = true;
        }

        // Check for the Prototype attribute
        $prototypeAttributes = $this->reflection()
            ?->getAttributes(AttributePrototype::class) ?? [];

        if (count($prototypeAttributes) > 0) {
            return $this->shouldPrototype = true;
        }

        return $this->shouldPrototype = false;
    }

}