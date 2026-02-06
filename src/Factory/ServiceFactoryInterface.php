<?php
namespace Juzdy\Container\Factory;

interface ServiceFactoryInterface
{
    /**
     * Create and return a service instance
     *
     * @return mixed
     */
    public function create(): mixed;
}