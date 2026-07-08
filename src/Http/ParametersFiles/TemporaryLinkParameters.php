<?php

namespace IlBronza\TemporaryLinks\Http\ParametersFiles;

use IlBronza\Form\Helpers\FieldsetsProvider\FieldsetParametersFile;
use IlBronza\TemporaryLinks\Models\TemporaryLink;

use function trans;

class TemporaryLinkParameters extends FieldsetParametersFile
{
	public function _getFieldsetsParameters() : array
	{
		return [
			'general' => [
				'translationPrefix' => 'temporarylinks::fields',
				'fields' => [
					'name' => ['text' => 'string|required|max:255'],
					'description' => ['textarea' => 'string|nullable|max:4096'],
				]
			],
			'destination' => [
				'translationPrefix' => 'temporarylinks::fields',
				'fields' => [
					'destination_type' => [
						'type' => 'select',
						'multiple' => false,
						'list' => [
							TemporaryLink::DESTINATION_TYPE_ROUTE => trans('temporarylinks::fields.destinationTypes.route'),
							TemporaryLink::DESTINATION_TYPE_URL => trans('temporarylinks::fields.destinationTypes.url'),
						],
						'rules' => 'string|required|in:' . TemporaryLink::DESTINATION_TYPE_ROUTE . ',' . TemporaryLink::DESTINATION_TYPE_URL,
					],
					'destination_route' => ['text' => 'string|nullable|max:255|required_if:destination_type,' . TemporaryLink::DESTINATION_TYPE_ROUTE],
					'destination_parameters' => ['textarea' => 'json|nullable'],
					'destination_url' => ['text' => 'url|nullable|max:2048|required_if:destination_type,' . TemporaryLink::DESTINATION_TYPE_URL],
				]
			],
			'validity' => [
				'translationPrefix' => 'temporarylinks::fields',
				'fields' => [
					'starts_at' => ['datetime' => 'date|nullable'],
					'expires_at' => ['datetime' => 'date|nullable|after:starts_at'],
					'max_visits' => ['number' => 'integer|nullable|min:1'],
					'consume_on_first_success' => ['boolean' => 'boolean|required'],
					'password' => ['text' => 'string|nullable|max:255'],
				]
			],
		];
	}
}
