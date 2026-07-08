<?php

namespace IlBronza\TemporaryLinks\Helpers;

use Illuminate\Support\Str;

use function config;
use function hash;
use function implode;
use function url;

class TokenHelper
{
	static function generate() : string
	{
		return Str::random(
			(int) config('temporarylinks.token_length')
		);
	}

	static function hash(string $plainToken) : string
	{
		return hash('sha256', $plainToken);
	}

	static function buildPublicUrl(string $plainToken) : string
	{
		return url(implode('/', [
			config('temporarylinks.publicPrefix'),
			$plainToken
		]));
	}
}
