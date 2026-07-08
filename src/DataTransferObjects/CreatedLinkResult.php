<?php

namespace IlBronza\TemporaryLinks\DataTransferObjects;

use IlBronza\TemporaryLinks\Helpers\TokenHelper;
use IlBronza\TemporaryLinks\Models\TemporaryLink;

class CreatedLinkResult
{
	public function __construct(
		public readonly TemporaryLink $link,
		public readonly string $plainToken
	)
	{
	}

	public function getPublicUrl() : string
	{
		return TokenHelper::buildPublicUrl($this->plainToken);
	}
}
