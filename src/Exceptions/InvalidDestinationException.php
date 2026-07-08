<?php

namespace IlBronza\TemporaryLinks\Exceptions;

use Exception;
use IlBronza\TemporaryLinks\Models\TemporaryLink;

class InvalidDestinationException extends Exception
{
	static function for(TemporaryLink $link) : static
	{
		return new static(
			"Invalid destination for temporary link {$link->getKey()}: type {$link->destination_type}"
		);
	}
}
