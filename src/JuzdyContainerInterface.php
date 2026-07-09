<?php
/**
 ▄▄▄
  █ J █ u z d y
   ▀▀▀
 */
namespace Juzdy\Container;

use Juzdy\Config\ConfigInterface;
use Psr\Container\ContainerInterface;

interface JuzdyContainerInterface extends ContainerInterface
{
    /**
     * Sets the configuration instance for the container.
     *
     * @param ConfigInterface $config The configuration instance to set.
     * 
     * @return static Returns the container instance for method chaining.
     */
    public function withConfig(ConfigInterface $config): static;

     /**
     * Creates an instance of the given class or interface, resolving its dependencies.
     *
     * @param string $id The class or interface name to create.
     * @param mixed ...$args Optional arguments to pass to the constructor.
     * 
     * @return mixed An instance of the requested class or interface.
     */
    public function create(string $id, ...$args): mixed;
}