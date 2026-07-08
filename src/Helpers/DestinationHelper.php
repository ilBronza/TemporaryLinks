<?php

namespace IlBronza\TemporaryLinks\Helpers;

use IlBronza\TemporaryLinks\Exceptions\InvalidDestinationException;
use IlBronza\TemporaryLinks\Models\TemporaryLink;
use Illuminate\Support\Facades\Route;

use function config;
use function count;
use function in_array;
use function parse_url;
use function route;

class DestinationHelper
{
	static function resolve(TemporaryLink $link) : string
	{
		if(! static::isValid($link))
			throw InvalidDestinationException::for($link);

		return match($link->destination_type)
		{
			TemporaryLink::DESTINATION_TYPE_ROUTE => static::resolveRoute($link),
			TemporaryLink::DESTINATION_TYPE_URL => $link->destination_url,
		};
	}

	static function isValid(TemporaryLink $link) : bool
	{
		return match($link->destination_type)
		{
			TemporaryLink::DESTINATION_TYPE_ROUTE => static::isValidRoute($link),
			TemporaryLink::DESTINATION_TYPE_URL => static::isValidAbsoluteUrl($link->destination_url),
			default => false,
		};
	}

	static function resolveRoute(TemporaryLink $link) : string
	{
		return route(
			$link->destination_route,
			$link->destination_parameters ?? []
		);
	}

	static function isValidRoute(TemporaryLink $link) : bool
	{
		if(! $link->destination_route)
			return false;

		return Route::has($link->destination_route);
	}

	static function isValidAbsoluteUrl(?string $url) : bool
	{
		if(! config('temporarylinks.allow_absolute_urls'))
			return false;

		if(! $url)
			return false;

		$parts = parse_url($url);

		if(! in_array($parts['scheme'] ?? null, ['http', 'https']))
			return false;

		if(isset($parts['user']) || isset($parts['pass']))
			return false;

		if(! ($parts['host'] ?? null))
			return false;

		return static::isAllowedHost($parts['host']);
	}

	static function isAllowedHost(string $host) : bool
	{
		$allowedHosts = config('temporarylinks.allowed_hosts');

		if(! count($allowedHosts))
			return true;

		return in_array($host, $allowedHosts);
	}
}
