<?php

use IlBronza\TemporaryLinks\Http\Controllers\CrudTemporaryLinkController;
use IlBronza\TemporaryLinks\Http\Controllers\TemporaryLinkActionsController;
use IlBronza\TemporaryLinks\Http\Controllers\TemporaryLinkRedirectController;
use IlBronza\TemporaryLinks\Http\ParametersFiles\TemporaryLinkParameters;
use IlBronza\TemporaryLinks\Models\TemporaryLink;
use IlBronza\TemporaryLinks\Models\TemporaryLinkAccess;

return [

	'routePrefix' => 'temporarylinksmanager',

	'publicPrefix' => 't',

	'middleware' => [
		'admin' => ['web', 'auth', 'role:administrator|superadmin'],
		'public' => ['web'],
	],

	'throttle' => [
		'public' => 'throttle:30,1',
		'password' => 'throttle:5,1',
	],

	'default_expires_in_minutes' => 1440,

	'token_length' => 64,

	'allow_absolute_urls' => false,

	'allowed_hosts' => [],

	'cleanup' => [
		'access_log_retention_days' => 90,
	],

	'models' => [
		'temporaryLink' => [
			'class' => TemporaryLink::class,
			'table' => 'temporarylinks__temporary_links',
			'controllers' => [
				'crud' => CrudTemporaryLinkController::class,
				'actions' => TemporaryLinkActionsController::class,
				'redirect' => TemporaryLinkRedirectController::class,
			],
			'parametersFiles' => [
				'create' => TemporaryLinkParameters::class,
				'edit' => TemporaryLinkParameters::class,
			],
		],
		'temporaryLinkAccess' => [
			'class' => TemporaryLinkAccess::class,
			'table' => 'temporarylinks__accesses',
		],
	],

];
