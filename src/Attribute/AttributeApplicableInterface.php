<?php
namespace Juzdy\Container\Attribute;

/**
 * Interface for attributes that can be applied to classes
 * This interface can be implemented by any attribute 
 * that needs to perform some action when applied to a class. 
 * The apply method can contain logic that modifies 
 * the behavior of the class or performs any necessary setup when the attribute is used.
 * 
 * Use this interface to create attributes that can be recognized and applied by the container's pipeline,
 * allowing for dynamic modification of class behavior based on the presence of specific attributes.
 *
 * @package Juzdy\Container\Attribute
 */
interface AttributeApplicableInterface
{
    public function apply(object $instance): void;
}