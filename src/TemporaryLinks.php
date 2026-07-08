<?php

namespace IlBronza\TemporaryLinks;

use IlBronza\CRUD\Traits\IlBronzaPackages\IlBronzaPackagesTrait;
use IlBronza\TemporaryLinks\Helpers\TemporaryLinkBuilder;
use IlBronza\TemporaryLinks\Models\TemporaryLink;

use function config;

class TemporaryLinks
{
	use IlBronzaPackagesTrait;

	static $packageConfigPrefix = 'temporarylinks';

	static function getTemporaryLinkClass() : string
	{
		return config('temporarylinks.models.temporaryLink.class');
	}

	static function create() : TemporaryLinkBuilder
	{
		return TemporaryLinkBuilder::create();
	}

	static function findByPlainToken(string $plainToken) : ?TemporaryLink
	{
		return static::getTemporaryLinkClass()::findByPlainToken($plainToken);
	}
}
