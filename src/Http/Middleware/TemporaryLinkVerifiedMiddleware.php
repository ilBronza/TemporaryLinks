<?php

namespace IlBronza\TemporaryLinks\Http\Middleware;

use Closure;
use IlBronza\TemporaryLinks\Helpers\SessionHelper;
use IlBronza\TemporaryLinks\Models\TemporaryLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use function abort;

class TemporaryLinkVerifiedMiddleware
{
	public function handle(Request $request, Closure $next)
	{
		if(! $link = $this->findGrantingLink($request))
			abort(403, trans('temporarylinks::messages.failures.destination_not_granted'));

		$request->attributes->set('temporaryLink', $link);

		return $next($request);
	}

	private function findGrantingLink(Request $request) : ?TemporaryLink
	{
		foreach(SessionHelper::getGrantedDestinations() as $linkId => $destinationUrl)
		{
			if(! $this->destinationMatchesRequest($destinationUrl, $request))
				continue;

			if(! $link = TemporaryLink::gpc()::find($linkId))
				continue;

			if($link->isRevoked())
				continue;

			if($link->isExpired())
				continue;

			return $link;
		}

		return null;
	}

	private function destinationMatchesRequest(string $destinationUrl, Request $request) : bool
	{
		return Str::before($destinationUrl, '?') == $request->url();
	}
}
