<?php

namespace IlBronza\TemporaryLinks\DataTransferObjects;

class OpeningResult
{
	private function __construct(
		public readonly bool $allowed,
		public readonly ?string $failureReason = null
	)
	{
	}

	static function allow() : static
	{
		return new static(true);
	}

	static function block(string $failureReason) : static
	{
		return new static(false, $failureReason);
	}

	public function isAllowed() : bool
	{
		return $this->allowed;
	}

	public function isBlocked() : bool
	{
		return ! $this->allowed;
	}
}
