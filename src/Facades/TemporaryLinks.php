<?php

namespace IlBronza\TemporaryLinks\Facades;

use Illuminate\Support\Facades\Facade;

class TemporaryLinks extends Facade
{
	protected static function getFacadeAccessor() : string
	{
		return 'temporarylinks';
	}
}
