<?php
namespace Juzdy\Container\Plugin;

interface PluginInterface
{
    /**
     * Invoke plugin on target
     *
     * @param mixed $target     The target object to process
     * @param callable $next    The next plugin in the chain
     *
     * @return mixed
     */
    public function __invoke(mixed $target, callable $next): mixed;
}