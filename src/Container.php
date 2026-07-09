<?php
/**
 ▄▄▄
  █ J █ u z d y
   ▀▀▀
 */
namespace Juzdy\Container;

use Psr\Container\ContainerInterface;
use Juzdy\Config\ConfigInterface;
use Juzdy\Container\Context\Context;
use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Exception\CircularDependencyException;
use Juzdy\Container\Exception\NotFoundException;
use Juzdy\Container\Pipeline\ContextPipeline;
use Juzdy\Container\Pipeline\Pipe\Configurator;
use Juzdy\Container\Pipeline\Pipe\DependencyCollector;
use Juzdy\Container\Pipeline\Pipe\ImplementationResolver;
use Juzdy\Container\Pipeline\Pipe\InstanceFetcher;
use Juzdy\Container\Pipeline\Pipe\Instantiator;
use Juzdy\Container\Pipeline\Pipe\LifeCycler;
use Juzdy\Container\Binder\BindingManager;
use Juzdy\Container\Repository\ShareManager;
use Throwable;

/**
 * Simple Dependency Injection Container
 *
 * @package Juzdy\Container
 */
class Container implements JuzdyContainerInterface
{
    /**
     * @var array<int, string> Stack of currently resolving services
     */
    protected array $stack = [];

    /**
     * @var array<string, bool> Currently resolving services
     */
    protected array $resolving = [];

    /**
     * @var array<string, string> System-wide shared services.
     */
    protected array $systemShares = [];

    /**
     * @var array<int, array<string, string>> Runtime preferences for interfaces
     */
    //protected array $propagatedPreferences = [];

    /**
     * @var ContextPipeline|null Pipeline for creating new service instances
     */
    private ?ContextPipeline $createServicePipeline = null;
    /**
     * @var ContextPipeline|null Pipeline for fetching existing service instances
     */
    private ?ContextPipeline $existingServicePipeline = null;

    private ?ConfigInterface $config = null;

    /**
     * Container constructor.
     * Initializes the container and registers default plugins.
     */
    public function __construct()
    {
        $this->setSystemShare(ShareManager::class, new ShareManager());
        $this->setSystemShare(BindingManager::class, new BindingManager());

        $this->preparePipelines();
        
    }

    protected function preparePipelines(): static
    {
        /**
         * The create pipeline is responsible for creating new instances when no existing instance is found.
         * It consists of several stages:
         * - ImplementationResolver: Determines the concrete class to instantiate based on the requested identifier and bindings.
         * - DependencyCollector: Collects the dependencies required to instantiate the class, using attributes and type hints.
         * - Instantiator: Creates the instance of the class, potentially using lazy loading or other instantiation strategies.
         * - Configurator: Applies any necessary configuration or injections after instantiation.
         * - LifeCycler: Handles lifecycle management, such as sharing the instance if applicable.
         */
        $this->createServicePipeline = (new ContextPipeline(
            
            new ImplementationResolver(
                new \Juzdy\Container\Pipeline\Pipe\Resolver\ImplementationCache(),
                new \Juzdy\Container\Pipeline\Pipe\Resolver\Concrete(),
                new \Juzdy\Container\Pipeline\Pipe\Resolver\BindingResolver(),
                new \Juzdy\Container\Pipeline\Pipe\Resolver\InterfaceConvention(),
            ),
            new DependencyCollector(
                new \Juzdy\Container\Pipeline\Pipe\DependencyCollector\UseParameterAttributePreference(),
                new \Juzdy\Container\Pipeline\Pipe\DependencyCollector\UseClassAttributePreference(),
                new \Juzdy\Container\Pipeline\Pipe\DependencyCollector\UseTypeHint(),
                new \Juzdy\Container\Pipeline\Pipe\DependencyCollector\DependencyNotFound()
            ),
            new Instantiator(
                new \Juzdy\Container\Pipeline\Pipe\Instantiator\LazyGhostInstance(),
                new \Juzdy\Container\Pipeline\Pipe\Instantiator\StandardInstance(),
                new \Juzdy\Container\Pipeline\Pipe\Instantiator\ReflectionInstance(),
                new \Juzdy\Container\Pipeline\Pipe\Instantiator\InstantiatorNotFound(),
            ),
            new Configurator(
                new \Juzdy\Container\Pipeline\Pipe\Configurator\Injector(),
                //new \Juzdy\Container\Pipeline\Pipe\Attribute\ApplyAttributes()
            ),
            new LifeCycler(
                new \Juzdy\Container\Pipeline\Pipe\LifeCycler\ShareIfApplicable()
            )
        ));

        /**
         * The existing pipeline is responsible for fetching existing instances from the container.
         * It checks for shared instances, bound instances, and other existing services before attempting to create a new instance.
         * This allows the container to return existing instances when available, improving performance and ensuring shared services
         */
        $this->existingServicePipeline = new ContextPipeline(
            new InstanceFetcher(
                new \Juzdy\Container\Pipeline\Pipe\Fetcher\SharedInstance(),
                new \Juzdy\Container\Pipeline\Pipe\Fetcher\Prototype()
            ),
        );

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withConfig(ConfigInterface $config): static
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get the configuration instance or a specific configuration value by key.
     *
     * @param string|null $key The configuration key to retrieve, or null to get the entire configuration instance.
     * 
     * @return mixed The configuration value associated with the given key, or the entire configuration instance if no key is provided.
     */
    protected function getConfig(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }

        return $this->config->get($key);
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $id): bool
    {
        try {
            $this->assertNotResolving($id)
                ->startResolving($id);

            return match (true) {
                $id === ContainerInterface::class,
                $id === static::class => true,
                $this->hasSystemShare($id) => true,
                $this->existing($id) !== null => true,
                default => false,
            };

        } catch (Throwable) {
            return false;

        } finally {

            $this->stopResolving($id);
            
        }
    }


    /**
     * {@inheritDoc}
     */
    public function get(string $id, ...$args): mixed
    {
        try {
            $this->assertNotResolving($id)
                ->startResolving($id);

            return match (true) {
                $id === ContainerInterface::class,
                $id === static::class 
                    => $this,
                $this->hasSystemShare($id) => $this->getSystemShare($id),
                ($instance = $this->existing($id)) !== null => $instance,
                default => $this->create($id, ...$args),
            };

        //} catch (Throwable $exception) {
        } catch (NotFoundException $exception) {
            throw new NotFoundException(
                "Service '$id' not found. Stack: " . implode(' -> ', array_values($this->stack)),
                0,
                $exception
            );

        } finally {

            $this->stopResolving($id);
            
        }
        
    }

    /**
     * Resolve a service by its identifier.
     * Checks for existing shared or bound instances.
     * Pipelines through resolvers and fetchers to find existing instances.
     * Returns the existing instance if found, or null if not found.
     * 
     * 
     */
    protected function existing(string $id): mixed
    {
        $context = ($this->existingServicePipeline)($this->serviceContext($id));
        
        return $context->instance();
    }

    /**
     * Create the service instance for the given identifier.
     * Pipelines through resolver, factory, and aware plugins.
     *
     * @param string $id The service identifier
     * 
     * @return mixed The created service instance
     */
    public function create(string $id, ...$args): mixed
    {
        $context = ($this->createServicePipeline)($this->serviceContext($id, ...$args));

        return $context->instance();

    }

    /**
     * Check if a service is currently being resolved.
     *
     * @param string $id The service identifier to check
     * 
     * @return bool True if the service is currently being resolved, false otherwise
     */
    private function isResolving(string $id): bool
    {
        return isset($this->resolving[$id]);
    }

    /**
     * Start resolving a service.
     *
     * @param string $id The service identifier to start resolving
     * 
     * @return static
     */
    private function startResolving(string $id): static
    {
        if (count($this->stack) > 100) {
            // @todo
        }

        $this->resolving[$id] = true;
        array_push($this->stack, $id);

        return $this;
    }

    /**
     * Stop resolving a service.
     *
     * @param string $id The service identifier to stop resolving
     * 
     * @return static
     */
    private function stopResolving(string $id): static
    {
        array_pop($this->stack);
        unset($this->resolving[$id]);

        return $this;
    }

    /**
     * Get the current resolution stack.
     *
     * @return array<int, string> The current stack of resolving services
     */
    public function stack(): array
    {
        return $this->stack;
    }

    /**
     * Check if a system-wide shared service exists for the given identifier.
     *
     * @param string $id The service identifier to check
     * @return bool True if a system-wide shared service exists for the given identifier, false otherwise
     */
    protected function hasSystemShare(string $id): bool
    {
        return isset($this->systemShares[$id]);
    }

    /**
     * Get the ShareManager instance from the container.
     *
     * @return ShareManager The ShareManager instance
     */
    protected function getSystemShare(string $id): mixed
    {
        return $this->systemShares[$id] ?? null;
    }

    /**
     * Set a system-wide shared service instance.
     *
     * @param string $id The identifier of the service to share
     * @param mixed $instance The instance of the service to share
     * 
     * @return static Returns the Container instance for method chaining
     */
    protected function setSystemShare(string $id, mixed $instance): static
    {
        $this->systemShares[$id] = $instance;

        return $this;
    }

    /**
     * Create a new context for the given service identifier.
     *
     * @param string $id The service identifier
     * 
     * @return ContextInterface The created context
     */
    protected function serviceContext(string $id/*, ...$args*/): ContextInterface
    {
        return 
            (new Context($id, $this))
                ->stack($this->stack())
                //->depends(...$args)
        ;
    }

    /**
     * Assert that the service is not currently being resolved to prevent circular dependencies.
     *
     * @param string $id The service identifier to check
     * 
     * @throws CircularDependencyException If the service is currently being resolved
     */
    protected function assertNotResolving(string $id): static
    {
        if ($this->isResolving($id)) {
            throw new CircularDependencyException(
                "Circular dependency detected while resolving service '{$id}'. Stack: " . implode(' -> ', array_values($this->stack)),
            );
        }

        return $this;
    }

    protected function profile(string $id)
    {
        //
    }

}