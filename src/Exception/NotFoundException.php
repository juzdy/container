<?php
namespace Juzdy\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends ContainerException implements ContainerExceptionInterface, NotFoundExceptionInterface
{
}