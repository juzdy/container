<?php

namespace Juzdy\Container\Repository;

use Juzdy\Container\Repository\RepositoryInterface;

/**
 * Repository for shared instances
 *
 * @package Juzdy\Container\Repository
 */
class SharedRepository implements RepositoryInterface
{
    /**
     * @var array<string, mixed> Array to store shared instances
     */
    private array $instances = [];

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
    public function set(string $id, mixed $instance): static
    {
        $this->instances[$id] = $instance;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function remove(string $id): static
    {
        unset($this->instances[$id]);

        return $this;
    }
}