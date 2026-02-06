<?php
namespace Juzdy\Container;

use Juzdy\Container\Plugin\PluginInterface;

class PluginManager implements PluginManagerInterface
{
    protected array $plugins = [];

    public function __construct(PluginInterface ...$plugins)
    {
        foreach ($plugins as $plugin) {
            $this->register($plugin);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function register(PluginInterface $plugin, int $priority = 0): static
    {
        $this->plugins[$priority][] = $plugin;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function process(mixed $target): mixed
    {
        krsort($this->plugins);

        $next = function (mixed $target) {
            return null;
        };

        foreach ($this->plugins as $priority => $plugins) {
            foreach ($plugins as $plugin) {
                $currentNext = $next;
                $next = function (mixed $target) use ($plugin, $currentNext) {
                    return $plugin($target, $currentNext);
                };
            }
        }

        return $next($target);
    }

        
}