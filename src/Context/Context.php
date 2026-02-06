<?php
namespace Juzdy\Container\Context;

use Juzdy\Container\JuzdyContainerInterface;
use ReflectionClass;
use ReflectionMethod;
use Traversable;

class Context implements ContextInterface
{
    /** @var mixed */
    protected mixed $instance = null;

    /** @var string|null */
    protected ?string $className = null;
    
    /**
     *  @var ReflectionClass|null 
     * 
     * Class reflection cache
     */
    protected ?ReflectionClass $reflectionClass = null;

    /**
     *  @var ReflectionMethod|null 
     * 
     * Constructor reflection cache
     */
    protected ?ReflectionMethod $constructor = null;

    /** @var array<int, mixed> */
    protected array $dependencies = [];

    /** @var array<string, mixed> */
    protected array $properties = [];


    /** 
     * @param string $className
     * @param JuzdyContainerInterface $container
     */
    public function __construct(
        private string $id, 
        private JuzdyContainerInterface $container
        )
    {
    }

    /**
     * Get the container associated with the context
     *
     * @return JuzdyContainerInterface
     */
    public function container(): JuzdyContainerInterface
    {
        return $this->container;
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
        }

        return $this->className;
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
        return $this->reflection()?->getConstructor();
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
    public function depends(...$dependencies): array
    {
        array_push($this->dependencies, ...$dependencies);

        return $this->dependencies;
    }

}