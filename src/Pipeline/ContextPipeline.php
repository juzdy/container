<?php
namespace Juzdy\Container\Pipeline;


class ContextPipeline
{
    /**
     * @var array<int, (ContextPipeInterface|string)[]>
     */
    private array $pipes = [];

    const PRIORITY_AUTO_STEP = 100;

    /**
     * @var (ContextPipeInterface|string)[]|null
     */
    private ?array $orderedPipesCache = null;

    public function __construct(ContextPipeInterface|string ...$pipes)
    {
        $priority = 0;
        foreach ($pipes as $pipe) {
            $this->pipe($pipe, $priority+= self::PRIORITY_AUTO_STEP);
        }
    }

    public function __invoke(mixed $target): mixed
    {
        return $this->process($target);
    }

    /**
     * Add a pipe to the pipeline.
     */
    public function pipe(ContextPipeInterface|string $pipe, int $priority = 0): static
    {
        $this->pipes[$priority][] = $pipe;
        $this->orderedPipesCache = null;

        return $this;
    }

    /**
     * Execute the pipeline with the given target and final callback.
     * @return mixed
     */
    public function process(mixed $target): mixed
    {
        $orderedPipes = $this->orderedPipes();

        $next = function ($t) {
            return $t;
        };

        $pipeline = array_reduce(
            array_reverse($orderedPipes),
            function ($next, $pipe) {
                $pipe = is_string($pipe) ? new $pipe() : $pipe;
                return fn ($target) => $pipe($target, $next);
            },
            $next
        );

        return $pipeline($target);
    }

    /**
     * @return ContextPipeInterface[]
     */
    private function orderedPipes(): array
    {
        if ($this->orderedPipesCache !== null) {
            return $this->orderedPipesCache;
        }

        if ($this->pipes === []) {
            return $this->orderedPipesCache = [];
        }

        $pipes = $this->pipes;
        ksort($pipes);

        $ordered = [];
        foreach ($pipes as $priorityPipes) {
            foreach ($priorityPipes as $pipe) {
                $ordered[] = $pipe;
            }
        }

        return $this->orderedPipesCache = $ordered;
    }

}