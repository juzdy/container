<?php
namespace Juzdy\Container\Attribute\Parameter;

use Attribute;

/**
 * Service preference attribute
 *
 * Used to mark a class as a preferred implementation for a given interface or abstract class.
 *
 * @package Juzdy\Container\Attribute
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Using
{
    public function __construct(
        public readonly ?string $preference = null,
    ) {
    }

    /**
     * Get the preferred implementation for a given interface or abstract class.
     *
     * @return string|null The preferred implementation class name
     */
    public function getPreference(): ?string
    {
        return $this->preference;
    }

}