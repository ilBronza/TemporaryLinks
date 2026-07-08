<?php

namespace IlBronza\TemporaryLinks;

use IlBronza\CRUD\Providers\RouterProvider\IbRouter;
use IlBronza\CRUD\Providers\RouterProvider\RoutedObjectInterface;
use IlBronza\CRUD\Traits\IlBronzaPackages\IlBronzaPackagesTrait;
use IlBronza\TemporaryLinks\Helpers\TemporaryLinkBuilder;
use IlBronza\TemporaryLinks\Models\TemporaryLink;

use function app;
use function config;

class TemporaryLinks implements RoutedObjectInterface
{
	use IlBronzaPackagesTrait;

	static $packageConfigPrefix = 'temporarylinks';

	public function manageMenuButtons()
	{
		if(! $menu = app('menu'))
			return;

		$settings = $menu->provideSettingsButton();

		$temporaryLinksManagerButton = $menu->createButton([
			'name' => 'temporaryLinksManager',
			'icon' => 'link',
			'text' => 'temporarylinks::temporarylinks.temporaryLinks'
		]);

		$settings->addChild($temporaryLinksManagerButton);

		$temporaryLinksManagerButton->addChild(
			$menu->createButton([
				'name' => 'temporaryLinks.index',
				'icon' => 'list',
				'text' => 'temporarylinks::temporarylinks.index',
				'href' => IbRouter::route($this, 'temporaryLinks.index')
			])
		);

		$temporaryLinksManagerButton->addChild(
			$menu->createButton([
				'name' => 'temporaryLinks.create',
				'icon' => 'plus',
				'text' => 'temporarylinks::temporarylinks.create',
				'href' => IbRouter::route($this, 'temporaryLinks.create')
			])
		);
	}

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
