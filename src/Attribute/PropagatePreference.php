<?php
namespace Juzdy\Container\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class PropagatePreference
{
    public function __construct(
        private array $preferences = [],
    ) {
    }

    public function getPreferences(): array
    {
        return $this->preferences;
    }
}