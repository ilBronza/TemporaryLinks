<?php

namespace IlBronza\TemporaryLinks\Helpers;

use IlBronza\TemporaryLinks\Models\TemporaryLink;
use IlBronza\TemporaryLinks\Models\TemporaryLinkAccess;
use Illuminate\Http\Request;

class AccessLoggerHelper
{
	static function logAllowed(TemporaryLink $link, Request $request, string $redirectedTo) : TemporaryLinkAccess
	{
		return static::log($link, $request, TemporaryLinkAccess::RESULT_ALLOWED, null, $redirectedTo);
	}

	static function logBlocked(?TemporaryLink $link, Request $request, string $failureReason) : TemporaryLinkAccess
	{
		return static::log($link, $request, TemporaryLinkAccess::RESULT_BLOCKED, $failureReason);
	}

	static function log(?TemporaryLink $link, Request $request, string $result, ?string $failureReason = null, ?string $redirectedTo = null) : TemporaryLinkAccess
	{
		return TemporaryLinkAccess::gpc()::create([
			'temporary_link_id' => $link?->getKey(),
			'accessed_at' => now(),
			'ip' => $request->ip(),
			'user_agent' => $request->userAgent(),
			'result' => $result,
			'failure_reason' => $failureReason,
			'redirected_to' => $redirectedTo,
		]);
	}
}
