<?php

namespace IlBronza\TemporaryLinks\Http\Controllers;

use IlBronza\CRUD\CRUD;

use function config;

abstract class TemporaryLinksPackageController extends CRUD
{
	public $configModelClassName = 'temporaryLink';

	public function getRouteBaseNamePrefix() : ?string
	{
		return config('temporarylinks.routePrefix');
	}

	public function setModelClass()
	{
		$this->modelClass = config("temporarylinks.models.{$this->configModelClassName}.class");
	}

	public function getModelClass() : string
	{
		$this->modelClass = config("temporarylinks.models.{$this->configModelClassName}.class");

		return $this->modelClass;
	}
}
