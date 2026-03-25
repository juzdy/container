<?php

namespace Juzdy\Container\Repository;

use Juzdy\Container\Attribute\Shared;

/**
 * Repository for shared instances
 *
 * @package Juzdy\Container\Repository
 */
#[Shared]
class ShareManager
{

    /**
     * @var array<string, mixed> Array to store shared instances
     */
    private array $instances = [];


    /**
     * ShareManager constructor.
     *
     * Initializes the shared repository and registers itself as a shared instance.
     */
    public function __construct()
    {

    }
    
    /**
     * {@inheritDoc}
     */
    public function get(string $id): mixed
    {
        return $this->instances[$id] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $id): bool
    {
        return isset($this->instances[$id]);
    }

    /**
     * {@inheritDoc}
     */
    public function share(string $id, mixed $instance): static
    {
        $this->instances[$id] = $instance;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function unshare(string $id): static
    {
        unset($this->instances[$id]);

        return $this;
    }
}