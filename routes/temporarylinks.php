<?php

use IlBronza\TemporaryLinks\Facades\TemporaryLinks;
use Illuminate\Support\Facades\Route;

Route::group([
	'middleware' => config('temporarylinks.middleware.admin'),
	'prefix' => 'temporary-links-management',
	'as' => config('temporarylinks.routePrefix'),
	'routeTranslationPrefix' => 'temporarylinks::routes.'
	],
	function()
	{
		$crudController = TemporaryLinks::getController('temporaryLink', 'crud');
		$actionsController = TemporaryLinks::getController('temporaryLink', 'actions');

		Route::post('temporary-links/{temporaryLink}/revoke', [$actionsController, 'revoke'])->name('temporaryLinks.revoke');
		Route::post('temporary-links/{temporaryLink}/reactivate', [$actionsController, 'reactivate'])->name('temporaryLinks.reactivate');
		Route::post('temporary-links/{temporaryLink}/extend', [$actionsController, 'extend'])->name('temporaryLinks.extend');
		Route::post('temporary-links/{temporaryLink}/regenerate-token', [$actionsController, 'regenerateToken'])->name('temporaryLinks.regenerateToken');
		Route::post('temporary-links/{temporaryLink}/duplicate', [$actionsController, 'duplicate'])->name('temporaryLinks.duplicate');
		Route::get('temporary-links/{temporaryLink}/preview', [$actionsController, 'preview'])->name('temporaryLinks.preview');

		Route::resource('temporary-links', $crudController)
			->names('temporaryLinks')
			->parameters(['temporary-links' => 'temporaryLink']);
	});

Route::group([
	'middleware' => array_merge(
		config('temporarylinks.middleware.public'),
		[config('temporarylinks.throttle.public')]
	),
	'prefix' => config('temporarylinks.publicPrefix'),
	'as' => 'temporarylinks.public.'
	],
	function()
	{
		$redirectController = TemporaryLinks::getController('temporaryLink', 'redirect');

		Route::get('{token}', [$redirectController, 'show'])->name('show');
		Route::post('{token}/proceed', [$redirectController, 'proceed'])->name('proceed');
		Route::post('{token}/password', [$redirectController, 'password'])
			->middleware(config('temporarylinks.throttle.password'))
			->name('password');
	});
