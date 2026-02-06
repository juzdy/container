<?php

namespace Juzdy\Container;

use Psr\Container\ContainerInterface;
use Juzdy\Container\Exception\NotFoundException;
use Juzdy\Container\Context\Context;
use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Exception\CircularDependencyException;
use Juzdy\Container\Plugin\Di\DiNotFound;
use Juzdy\Container\Plugin\Di\UseClassAttribute;
use Juzdy\Container\Plugin\Di\UseParameterAttribute;
use Juzdy\Container\Plugin\Di\UseType;
use Juzdy\Container\Plugin\PluginInterface;
use Juzdy\Container\Plugin\Factory\FallbackFactory;
use Juzdy\Container\Plugin\Factory\LazyGhostFactory;
use Juzdy\Container\Plugin\Factory\StandardFactory;
use Juzdy\Container\Plugin\LifeCycle\Prototype;
use Juzdy\Container\Plugin\LifeCycle\Shared;
use Juzdy\Container\Plugin\Resolver\Concrete;
use Juzdy\Container\Plugin\Resolver\InterfaceConvention;
use Juzdy\Container\Plugin\Resolver\WireResolver;
use Throwable;

/**
 * Simple Dependency Injection Container
 *
 * @package Juzdy\Container
 */
class Container implements JuzdyContainerInterface
{
    
    /**
     * @var array<string, mixed> Registered shared services
     */
    protected array $shared = [];

    /**
     * @var array<int, string> Stack of currently resolving services
     */
    protected array $stack = [];

    /**
     * @var array<string, bool> Currently resolving services
     */
    protected array $resolving = [];

    /**
     * @var array<int, array<string, string>> Runtime preferences for interfaces
     */
    protected array $propagatedPreferences = [];

    /**
     * @var PluginManagerInterface|null The resolve plugin manager.
     * 
     * Resolves id to service concrete class.
     */
    protected ?PluginManagerInterface $resolveManager = null;
    
    /**
     * @var PluginManagerInterface|null The resolve plugin manager.
     * 
     * Resolves dependencies.
     */
    protected ?PluginManagerInterface $diManager = null;

    /**
     * @var PluginManagerInterface|null The factory plugin manager
     * 
     * Processes service creation.
     */
    protected ?PluginManagerInterface $factoryManager = null;

    /**
     * @var PluginManagerInterface|null The aware plugin manager.
     * 
     * Processes awares. e.g., dependency injection by setter methods.
     */
    protected ?PluginManagerInterface $awareManager = null;

    /**
     * @var PluginManagerInterface|null The lifecycle plugin manager.
     * 
     * Processes lifecycle management of services.
     */
    protected ?PluginManagerInterface $lifecycleManager = null;

    /**
     * @var PluginManagerInterface|null The fetch plugin manager.
     * 
     * Processes shared services on fetch.
     */
    protected ?PluginManagerInterface $fetchManager = null;

    /**
     * Container constructor.
     * Initializes the container and registers default plugins.
     */
    public function __construct()
    {
        $this->initPlugins();
    }

    /**
     * Initialize and register default plugins
     *
     * @return static
     */
    protected function initPlugins(): static
    {
        $this->getFetchManager(
            new Prototype(),          //First registered, last executed
        );

        $this->getResolveManager(
            new Concrete(),
            new WireResolver(),              
            new InterfaceConvention(),
        );

        $this->getDiManager(
            new DiNotFound(),
            new UseType(),
            //new AttributeClassPropagated(),
            new UseClassAttribute(),
            new UseParameterAttribute(),
        );

        $this->getFactoryManager(
            new FallbackFactory(),
            new StandardFactory(),
            new LazyGhostFactory(),
            //new CustomFactory()
        );

        $this->getAwareManager(
            new Plugin\Aware\Injector()
        );

        $this->getLifecycleManager(
            new Shared()
        );        
        return $this;
    }

    public function propagatePreference(string $id, string $preference): static
    {
        array_unshift(
            $this->propagatedPreferences[$id] ??= [],
            $preference
        );

        return $this;
    }

    public function getPropagatedPreferences(): array
    {
        return $this->propagatedPreferences;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $id): bool
    {
        return 
            is_a($id, ContainerInterface::class, true)
            || $this->hasShared($id)
            || $this->can($id)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function can(string $id): bool
    {
        try {

            $context = $this->context($id);
            $this->resolve($context);

            $ref = $context->reflection();
            if ($ref !== null && $ref->isInstantiable()) {
                return true;
            }

        } catch (Throwable) {
            // Ignore exceptions and return false
            // Means the service cannot be resolved/created
        }

        return false;
    }


    /**
     * {@inheritDoc}
     */
    public function get(string $id): mixed
    {
        if (isset($this->resolving[$id])) {
            throw new CircularDependencyException('Circular dependency detected while resolving service ' . $id . '. Stack: ' . implode(' -> ', $this->stack) . ' -> ' . $id);
        }

        array_push($this->stack, $id);
        $this->resolving[$id] = true;

        try {
            $service = match (true) {
                $id === ContainerInterface::class => $this,
                $this->hasShared($id) => $this->fetch($id),
                default => $this->create($id),
            };
        } 
        catch (Throwable $ex) {
            throw $ex;
        }
        finally {
            array_pop($this->stack);
            unset($this->resolving[$id]);
        }

        return $service;
    }

    /**
     * {@inheritDoc}
     */
    public function share(string $id, mixed $instance): static
    {
        $this->shared[$id] = $instance;

        return $this;
    }

    public function stack(): array
    {
        return $this->stack;
    }
    
    /**
     * Require the service with the given identifier.
     * Pipelines through require plugins.
     *
     * @param string $id The service identifier
     * 
     * @return mixed The required service instance
     */
    protected function fetch(string $id): mixed
    {
        $service = $this->getShared($id);
        $context = $this->context($id);

        $context
            ->instance($service);
        
        $this->getFetchManager()
                ->process($context);

        return $context->instance();
    }

    /**
     * Check if the local service is registered.
     * 
     * @param string $id The service identifier
     * 
     * @return bool True if the local service is registered, false otherwise
     */
    protected function hasShared(string $id): bool
    {
        return array_key_exists($id, $this->shared);
    }

    
    /**
     * Get the local shared service.
     * 
     * @param string $id The service identifier
     * 
     * @return mixed The local shared service instance
     */
    protected function getShared(string $id): mixed
    {
        return $this->shared[$id];
    }

    /**
     * Forget a shared service.
     */
    protected function forgetShared(string $id): static
    {
        unset($this->shared[$id]);

        return $this;
    }

    /**
     * Create the service instance for the given identifier.
     * Pipelines through resolver, factory, and aware plugins.
     *
     * @param string $id The service identifier
     * 
     * @return mixed The created service instance
     */
    protected function create(string $id): mixed
    {
        $context = $this->context($id);

        return $this
            ->resolve($context)
            ->di($context, false)
            ->factory($context)
            ->aware($context)
            ->lifecycle($context)
            ->instance($context);
    }

    /**
     * Get the instance from the context.
     *
     * @param ContextInterface $context The context to get the instance from
     * 
     * @return mixed The instance from the context
     */
    protected function instance(ContextInterface $context): mixed
    {
        return $context->instance();
    }

    /**
     * Resolve the service for the context.
     *
     * @param ContextInterface $context The context to resolve
     * 
     * @return static
     */
    protected function resolve(ContextInterface $context): static
    {
        /**
         * Resolve the service class for the context using resolver plugins.
         */
        $this->getResolveManager()
            ->process($context);

        if (!$context->class()) {
            throw new NotFoundException("Cannot resolve service '{$context->id()}'.");
        }

        if (!class_exists($context->class())) {
            throw new NotFoundException("Resolved class '{$context->class()}' for service '{$context->id()}' does not exist.");
        }

        if (!$context->reflection()?->isInstantiable()) {
            throw new NotFoundException("Resolved class '{$context->class()}' for service '{$context->id()}' is not instantiable.");
        }

        return $this;
    }

    /**
     * Resolve dependencies for the context.
     *
     * @param ContextInterface $context The context to resolve dependencies for
     * @param bool $dry Whether to perform a dry run (only check if dependencies can be resolved)
     * 
     * @return static
     */
    protected function di(ContextInterface $context, bool $dry): static
    {
        foreach ($context->params() as $param) {
            $dep = $this->getDiManager()
                ->process(
                    $context
                        ->property(ContextInterface::PROPERTY_CURRENT_PARAMETER, $param)
                        //->property(ContextInterface::PROPERTY_DRY_RUN, $dry)
                );

            $context->depends($dep);
        }

        return $this;
    }

    /**
     * Process factory plugins for the context.
     *
     * @param ContextInterface $context The context to process factory plugins for
     * 
     * @return static
     */
    protected function factory(ContextInterface $context): static
    {
        $instance = $this->getFactoryManager()
                ->process($context);

        $context->instance($instance);

        return $this;
    }

    /**
     * Process aware plugins for the context.
     *
     * @param ContextInterface $context The context to process aware plugins for
     * 
     * @return static
     */
    protected function aware(ContextInterface $context): static
    {
        $this->getAwareManager()
                ->process($context);

        return $this;
    }

    /**
     * Process lifecycle plugins for the context.
     *
     * @param ContextInterface $context The context to process lifecycle plugins for
     * 
     * @return static
     */
    protected function lifecycle(ContextInterface $context): static
    {
        $this->getLifecycleManager()
                ->process($context);

        return $this;
    }

    /**
     * Get or create the resolve plugin manager.
     * 
     * @param PluginInterface ...$plugins Plugins to register
     *
     * @return PluginManagerInterface The resolve plugin manager
     */
    protected function getResolveManager(PluginInterface ...$plugins): PluginManagerInterface
    {
        if ($this->resolveManager === null) {
            $this->resolveManager = new PluginManager(...$plugins);
        }

        return $this->resolveManager;
    }

    /**
     * Get or create the resolve plugin manager.
     * 
     * @param PluginInterface ...$plugins Plugins to register
     *
     * @return PluginManagerInterface The resolve plugin manager
     */
    protected function getDiManager(PluginInterface ...$plugins): PluginManagerInterface
    {
        if ($this->diManager === null) {
            $this->diManager = new PluginManager(...$plugins);
        }

        return $this->diManager;
    }

    /**
     * Get or create the factory plugin manager.
     * 
     * @param PluginInterface ...$plugins Plugins to register
     *
     * @return PluginManagerInterface The factory plugin manager
     */
    protected function getFactoryManager(PluginInterface ...$plugins): PluginManagerInterface
    {
        if ($this->factoryManager === null) {
            $this->factoryManager = new PluginManager(...$plugins);
        }

        return $this->factoryManager;
    }

    /**
     * Get or create the aware plugin manager.
     * 
     * @param PluginInterface ...$plugins Plugins to register
     *
     * @return PluginManagerInterface The aware plugin manager
     */
    protected function getAwareManager(PluginInterface ...$plugins): PluginManagerInterface
    {
        if ($this->awareManager === null) {
            $this->awareManager = new PluginManager(...$plugins);
        }

        return $this->awareManager;
    }


    /**
     * Get or create the lifecycle plugin manager.
     * 
     * @param PluginInterface ...$plugins Plugins to register
     *
     * @return PluginManagerInterface The lifecycle plugin manager
     */
    protected function getLifecycleManager(PluginInterface ...$plugins): PluginManagerInterface
    {
        if ($this->lifecycleManager === null) {
            $this->lifecycleManager = new PluginManager(...$plugins);
        }

        return $this->lifecycleManager;
    }

    /**
     * Get or create the require plugin manager.
     * 
     * @param PluginInterface ...$plugins Plugins to register
     *
     * @return PluginManagerInterface The require plugin manager
     */
    protected function getFetchManager(PluginInterface ...$plugins): PluginManagerInterface
    {
        
        if ($this->fetchManager === null) {
            $this->fetchManager = new PluginManager(...$plugins);
        }

        return $this->fetchManager;
    }

    /**
     * Create a new context for the given service identifier.
     *
     * @param string $id The service identifier
     * 
     * @return ContextInterface The created context
     */
    protected function context(string $id, ): ContextInterface
    {
        return new Context($id, $this);
    }
}