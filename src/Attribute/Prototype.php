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
class Prototype
{
}