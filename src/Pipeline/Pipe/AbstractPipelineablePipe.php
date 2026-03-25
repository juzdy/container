<?php
namespace Juzdy\Container\Pipeline\Pipe;

use Juzdy\Container\Pipeline\ContextPipeInterface;
use Juzdy\Container\Pipeline\ContextPipeline;

abstract class AbstractPipelineablePipe implements ContextPipeInterface
{
    /**
     * @var ContextPipeline|null
     */
    protected ?ContextPipeline $pipeline = null;

    /**
     * @param ContextPipeInterface|string ...$pipes
     */
    public function __construct(ContextPipeInterface|string ...$pipes)
    {
        $this->pipeline = new ContextPipeline(...$pipes);
    }

    /**
     * @return ContextPipeline
     */
    protected function getPipeline(): ContextPipeline
    {
        return $this->pipeline;
    }
}