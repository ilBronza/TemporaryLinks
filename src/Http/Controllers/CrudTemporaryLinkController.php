<?php

namespace IlBronza\TemporaryLinks\Http\Controllers;

use IlBronza\CRUD\Traits\CRUDCreateStoreTrait;
use IlBronza\CRUD\Traits\CRUDDeleteTrait;
use IlBronza\CRUD\Traits\CRUDDestroyTrait;
use IlBronza\CRUD\Traits\CRUDEditUpdateTrait;
use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\CRUD\Traits\CRUDPlainIndexTrait;
use IlBronza\CRUD\Traits\CRUDRelationshipTrait;
use IlBronza\CRUD\Traits\CRUDShowTrait;
use IlBronza\TemporaryLinks\Http\ParametersFiles\TemporaryLinkParameters;
use IlBronza\TemporaryLinks\Models\TemporaryLink;
use Illuminate\Http\Request;

use function config;
use function session;
use function trans;

class CrudTemporaryLinkController extends TemporaryLinksPackageController
{
	use CRUDShowTrait;
	use CRUDPlainIndexTrait;
	use CRUDIndexTrait;
	use CRUDEditUpdateTrait;
	use CRUDCreateStoreTrait;
	use CRUDDeleteTrait;
	use CRUDDestroyTrait;
	use CRUDRelationshipTrait;

	public $parametersFile = TemporaryLinkParameters::class;

	public static $tables = [
		'index' => [
			'translationPrefix' => 'temporarylinks::fields',
			'fields' => [
				'mySelfEdit' => 'links.edit',
				'mySelfSee' => 'links.see',
				'name' => 'flat',
				'translated_status' => 'flat',
				'destination_type' => 'flat',
				'starts_at' => [
					'type' => 'dates.datetime',
				],
				'expires_at' => [
					'type' => 'dates.datetime',
				],
				'visits_count' => 'flat',
				'max_visits' => 'flat',
				'mySelfDelete' => 'links.delete',
			]
		]
	];

	public $showMethodRelationships = ['accesses'];

	public $allowedMethods = [
		'index',
		'show',
		'edit',
		'update',
		'create',
		'store',
		'destroy',
	];

	public function getRouteBaseNamePieces()
	{
		return [
			config('temporarylinks.routePrefix') . 'temporaryLinks'
		];
	}

	public function getIndexElements()
	{
		return $this->getModelClass()::all();
	}

	public function performAdditionalOperations()
	{
		if(! $this->modelInstance)
			return;

		if(! $plainToken = $this->modelInstance->lastGeneratedPlainToken)
			return;

		session()->flash('temporarylinks.plainUrl', $this->modelInstance->getPublicUrl($plainToken));

		session()->flash('message', trans('temporarylinks::messages.linkCreated', [
			'url' => $this->modelInstance->getPublicUrl($plainToken),
		]));
	}

	public function show(TemporaryLink $temporaryLink)
	{
		return $this->_show($temporaryLink);
	}

	public function edit(TemporaryLink $temporaryLink)
	{
		return $this->_edit($temporaryLink);
	}

	public function update(Request $request, TemporaryLink $temporaryLink)
	{
		return $this->_update($request, $temporaryLink);
	}

	public function destroy(TemporaryLink $temporaryLink)
	{
		return $this->_destroy($temporaryLink);
	}
}
