<?php
namespace Juzdy\Container;

use Juzdy\Container\Plugin\PluginInterface;

interface PluginManagerInterface
{
    /**
     * Register a plugin with a given name, PluginInterface, and priority
     * 
     * @param PluginInterface $plugin  The plugin instance to register
     * @param int $priority     The priority of the plugin
     * 
     * @return static           The current instance for method chaining
     */
    public function register(PluginInterface $plugin, int $priority = 0): static;

    /**
     * Process the target object through all registered plugins
     * 
     * @param mixed $target The target object to process
     * 
     * @return mixed         The processed target object or result or null
     */
    public function process(mixed $target): mixed;
}