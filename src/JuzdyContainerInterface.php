<?php
namespace Juzdy\Container;

use Psr\Container\ContainerInterface;

interface JuzdyContainerInterface extends ContainerInterface
{
    /**
     * Share an instance in the container.
     *
     * @param string $id
     * @param mixed $instance
     * 
     * @return static
     */
    public function share(string $id, mixed $instance): static;

    /**
     * Check if the container can return an entry for the given identifier.
     *
     * @param string $id
     * 
     * @return bool
     */
    public function can(string $id): bool;
}