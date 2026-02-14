<?php
namespace Juzdy\Container\Repository;

interface RepositoryInterface
{
    /**
     * Get an instance by its identifier.
     *
     * @param string $id The identifier of the instance.
     * @return mixed The instance associated with the identifier, or null if not found.
     */
    public function get(string $id): mixed;

    /**
     * Check if an instance exists for the given identifier.
     *
     * @param string $id The identifier to check.
     * @return bool True if an instance exists for the identifier, false otherwise.
     */
    public function has(string $id): bool;

    /**
     * Set an instance for a given identifier.
     *
     * @param string $id The identifier to associate with the instance.
     * @param mixed $instance The instance to store.
     * @return static Returns the repository instance for method chaining.
     */
    public function set(string $id, mixed $instance): static;

    /**
     * Unset an instance for a given identifier.
     *
     * @param string $id The identifier of the instance to unset.
     * 
     * @return static Returns the repository instance for method chaining.
     */
    public function remove(string $id): static;
}