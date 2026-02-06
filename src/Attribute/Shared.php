<?php
namespace Juzdy\Container\Attribute;

use Attribute;

/**
 * Shared service attribute
 *
 * Used to mark a service as shared (singleton) within the container.
 *
 * @package Juzdy\Container\Attribute
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Shared
{
    public function __construct(
        public readonly bool $isShared = true
    ) {
    }

    /**
     * Check if the service is marked as shared
     *
     * @return bool True if the service is shared, false otherwise
     */
    public function isShared(): bool
    {
        return $this->isShared;
    }
}