<?php
/**
 ▄▄▄
  █ J █ u z d y
   ▀▀▀
 */
namespace Juzdy\Container;

use Psr\Container\ContainerInterface;
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
use Juzdy\Container\Repository\BindingManager;
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
     * Container constructor.
     * Initializes the container and registers default plugins.
     */
    public function __construct()
    {
        $this->setSystemShare(ShareManager::class, new ShareManager());
        $this->setSystemShare(BindingManager::class, new BindingManager());
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
                $id === ContainerInterface::class => true,
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
    public function get(string $id): mixed
    {
        try {
            $this->assertNotResolving($id)
                ->startResolving($id);

            return $service = match (true) {
                $id === ContainerInterface::class => $this,
                $id === static::class => $this,
                $this->hasSystemShare($id) => $this->getSystemShare($id),
                ($instance = $this->existing($id)) !== null => $instance,
                default => $this->create($id),
            };

        //} catch (Throwable $exception) {
        } catch (NotFoundException $exception) {
            throw new NotFoundException(
                "Service '$id' not found.",
                [
                    'service' => $id,
                    'stack' => array_values($this->stack),
                ],
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
        $pipeline = new ContextPipeline(
            new ImplementationResolver(
                \Juzdy\Container\Pipeline\Pipe\Resolver\Concrete::class,
                \Juzdy\Container\Pipeline\Pipe\Resolver\BindingResolver::class,
                \Juzdy\Container\Pipeline\Pipe\Resolver\InterfaceConvention::class
            ),
            new InstanceFetcher(
                \Juzdy\Container\Pipeline\Pipe\Fetcher\SharedInstance::class,
                \Juzdy\Container\Pipeline\Pipe\Fetcher\Prototype::class
            ),
        );

        $context = $pipeline($this->serviceContext($id));

        
        return $context->instance();
    }

    // protected function _existing(string $id): mixed
    // {
    //     if ($id == ConfigInterface::class) {
    //         return $this->_existing($id);
    //     }
    //     $sharedRepo = $this->getShareManager();

    //     if ($sharedRepo->has($id)) {
    //         return $sharedRepo->get($id);
    //     }

    //     return null;
    // }

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
        $pipeline = (new ContextPipeline(
            new ImplementationResolver(
                \Juzdy\Container\Pipeline\Pipe\Resolver\Concrete::class,
                \Juzdy\Container\Pipeline\Pipe\Resolver\BindingResolver::class,
                \Juzdy\Container\Pipeline\Pipe\Resolver\InterfaceConvention::class
            ),
            new DependencyCollector(
                \Juzdy\Container\Pipeline\Pipe\DependencyCollector\UseParameterAttributePreference::class,
                \Juzdy\Container\Pipeline\Pipe\DependencyCollector\UseClassAttributePreference::class,
                \Juzdy\Container\Pipeline\Pipe\DependencyCollector\UseTypeHint::class,
                \Juzdy\Container\Pipeline\Pipe\DependencyCollector\DependencyNotFound::class
            ),
            new Instantiator(
                \Juzdy\Container\Pipeline\Pipe\Instantiator\LazyGhostInstance::class,
                \Juzdy\Container\Pipeline\Pipe\Instantiator\StandardInstance::class,
                \Juzdy\Container\Pipeline\Pipe\Instantiator\ReflectionInstance::class,
                \Juzdy\Container\Pipeline\Pipe\Instantiator\InstantiatorNotFound::class,
            ),
            new Configurator(
                \Juzdy\Container\Pipeline\Pipe\Configurator\Injector::class
            ),
            new LifeCycler(
                \Juzdy\Container\Pipeline\Pipe\LifeCycler\ShareIfApplicable::class
            )
        ));

        $context = $pipeline($this->serviceContext($id));

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
            //todo    
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
     * Get the ShareManager instance from the container.
     *
     * @return ShareManager The ShareManager instance
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
     * @param ContextInterface $context
     * @param string $stage
     * @return array<string, mixed>
     */
    protected function exceptionContext(ContextInterface $context, string $stage): array
    {
        return [
            'stage' => $stage,
            'service' => $context->id(),
            'class' => $context->class(),
            'stack' => array_values($this->stack),
        ];
    }

    /**
     * Create a new context for the given service identifier.
     *
     * @param string $id The service identifier
     * 
     * @return ContextInterface The created context
     */
    protected function serviceContext(string $id, ): ContextInterface
    {
        return 
            (new Context($id, $this))
        //        ->stack($this->stack)
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
                'Circular dependency detected while resolving service ' . $id . '.',
                [
                    'service' => $id,
                    'stack' => array_values($this->stack),
                ]
            );
        }

        return $this;
    }

}