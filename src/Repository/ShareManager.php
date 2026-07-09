<?php

namespace Juzdy\Container\Repository;

use Juzdy\Container\Attribute\Shared;
use Juzdy\Container\Contract\Lifecycle\SharedInterface;

/**
 * Repository for shared instances
 *
 * @package Juzdy\Container\Repository
 */
#[Shared]
class ShareManager implements SharedInterface
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
    public function share(string|array $id, mixed $instance): static
    {
        match (true) {
            is_array($id) => $this->shareMultiple($id, $instance),
            default => $this->instances[$id] = $instance,
        };

        return $this;
    }

    protected function shareMultiple(array $ids, mixed $instance): void
    {
        foreach ($ids as $singleId) {
            if (!is_string($singleId)) {
                throw new \InvalidArgumentException("ShareManager: All IDs must be strings");
            }
            $this->instances[$singleId] = $instance;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function unshare(string|array $id): static
    {
        if (is_array($id)) {
            foreach ($id as $singleId) {
                if (!is_string($singleId)) {
                    throw new \InvalidArgumentException("ShareManager: All IDs must be strings");
                }
                unset($this->instances[$singleId]);
            }
        } else {
            unset($this->instances[$id]);
        }

        return $this;
    }
}