<?php

namespace Juzdy\Container\Binder;

use Closure;
use Juzdy\Config\ConfigInterface;
use Juzdy\Container\Attribute\Shared;
use Juzdy\Container\Contract\Lifecycle\SharedInterface;
use Juzdy\Container\Binder\Definition;

/**
 * Manager for bounds
 */
#[Shared]
class BindingManager implements SharedInterface
{
    
    /**
     * @var array<string, Definition> Array to store bound instances
     */
    private array $definitions = [];

    /**
     * Configure the binding manager with bindings from the configuration.
     *
     * @param ConfigInterface $config The configuration instance to retrieve bindings from
     */
    public function configure(ConfigInterface $config): void
    {
        foreach($config('container.bindings') ?? [] as $id => $to) {
            $this->bind($id, $to);
        }
    }

    /**
     * Bind a class or interface to a concrete implementation or factory.
     *
     * @param string $id The identifier of the service to bind
     * @param string|Closure $to The concrete class name or factory closure to bind to the identifier
     * 
     * @return static Returns the BindingManager instance for method chaining
     */
    public function bind(string $id, string|Closure $to): static
    {
        $this->definitions[$id] = (new Definition($id))->to($to);

        return $this;
    }

    /**
     * @deprecated Use bindMany() instead.
     * Bind multiple classes or interfaces to their concrete implementations or factories.
     *
     * @param array<string, string|Closure> $bindings An associative array where the key is the identifier and the value is the concrete class name or factory closure.
     * 
     * @return static Returns the BindingManager instance for method chaining
     */
    public function binds(array $bindings): static
    {
        foreach ($bindings as $id => $to) {
            $this->bind($id, $to);
        }

        return $this;
    }

    /**
     * Bind multiple classes or interfaces to their concrete implementations or factories.
     *
     * @param array<string, string|Closure> $bindings An associative array where the key is the identifier and the value is the concrete class name or factory closure.
     * 
     * @return static Returns the BindingManager instance for method chaining
     */
    public function bindMany(array $bindings): static
    {
        foreach ($bindings as $id => $to) {
            $this->bind($id, $to);
        }

        return $this;
    }

     

    /**
     * Check if a binding exists for the given identifier.
     * @param string $id The identifier to check for a binding
     * 
     * @return bool Returns true if a binding exists, false otherwise
     */
    public function has(string $id): bool
    {
        return isset($this->definitions[$id]);
    }

    /**
     * Get the binding for the given identifier.
     * @param string $id The identifier to get the binding for
     * 
     * @return mixed The binding for the given identifier
     * 
     * @throws \LogicException if no binding is found for the given ID
     */
    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new \LogicException("No binding found for '{$id}'.");
        }

        $definition = $this->definitions[$id];
        
        return $definition->binding();
    }



}