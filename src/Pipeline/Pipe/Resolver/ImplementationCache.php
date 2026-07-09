<?php
namespace Juzdy\Container\Pipeline\Pipe\Resolver;

use Juzdy\Container\Context\ContextInterface;
use Juzdy\Container\Pipeline\ContextPipeInterface;
use Psr\SimpleCache\CacheInterface;

class ImplementationCache implements ContextPipeInterface
{

    // public function __construct(
    //     private CacheInterface $cache
    // )
    // {
        
    // }

    public function __invoke(ContextInterface $context, callable $next): ContextInterface
    {
        
        // $cacheKey = $this->generateCacheKey($context);

        // if ($this->cache->has($cacheKey)) {
        //     $context->class($this->cache->get($cacheKey));
        //     die('Cache hit for ' . $context->id() . ' with key ' . $cacheKey);
        //     return $context;
        // }

        $context = $next($context);

        // if ($context->class() !== null) {
        //     $this->cache->set($cacheKey, $context->class());
        //     echo '<br>Cache stored for ' . $context->id() . ' with key ' . $cacheKey . ' => ' . $context->class() . '<br>';
        // }

        return $context;
    }

    private function generateCacheKey(ContextInterface $context): string
    {
        // Generate a unique cache key based on the context's ID and stack
        return $context->id() . '|' . implode('>', $context->stack());
    }
}