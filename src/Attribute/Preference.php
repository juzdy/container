<?php
namespace Juzdy\Container\Attribute;

use Attribute;

/**
 * Service preference attribute
 *
 * Used to mark a class as a preferred implementation for a given interface or abstract class.
 *
 * @package Juzdy\Container\Attribute
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Preference
{
    public function __construct(
        public readonly array $preferences = [],
    ) {
    }

    /**
     * Get the preferred implementation for a given interface or abstract class.
     *
     * @param string $for The interface or abstract class name
     *
     * @return string|null The preferred implementation class name
     */
    public function getPreference(string $for): ?string
    {
        return $this->preferences[$for] ?? null;
    }

}