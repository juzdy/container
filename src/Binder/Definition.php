<?php

namespace Juzdy\Container\Binder;

use Closure;

class Definition
{
    private ?Closure $to = null;

    public function __construct(private string $id) {}

    public function id(): string
    {
        return $this->id;
    }

    public function to(string|Closure $to): static
    {
        $this->to = is_string($to) ? function() use ($to) { return $to; } : $to;
        
        return $this;
    }

    public function binding(): string
    {
        if ($this->to === null) {
            throw new \LogicException("No binding defined.");
        }

        return ($this->to)();
    }

}