<?php

namespace Juzdy\Container\Exception;

/**
 * Base container exception with context data.
 */
class ContainerException extends \RuntimeException implements ContainerExceptionInterface
{
	/**
	 * @var array<string, mixed>
	 */
	private array $context = [];

	/**
	 * @param string $message
	 * @param array<string, mixed> $context
	 * @param int $code
	 * @param \Throwable|null $previous
	 */
	public function __construct(
		string $message,
		array $context = [],
		int $code = 0,
		?\Throwable $previous = null
	) {
		parent::__construct($message, $code, $previous);
		$this->context = $context;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getContext(): array
	{
		return $this->context;
	}
}