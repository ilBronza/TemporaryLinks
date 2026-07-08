<?php

namespace IlBronza\TemporaryLinks\Events;

use IlBronza\TemporaryLinks\Models\TemporaryLink;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TemporaryLinkBlocked
{
	use Dispatchable;
	use SerializesModels;

	public function __construct(
		public ?TemporaryLink $link,
		public string $failureReason
	)
	{
	}
}
