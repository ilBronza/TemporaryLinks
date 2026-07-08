<?php

namespace IlBronza\TemporaryLinks\Models\Traits;

use IlBronza\Buttons\Button;

use function trans;

trait TemporaryLinkButtonsRoutesTrait
{
	public function getRevokeUrl() : string
	{
		return $this->getKeyedRoute('revoke');
	}

	public function getReactivateUrl() : string
	{
		return $this->getKeyedRoute('reactivate');
	}

	public function getExtendUrl() : string
	{
		return $this->getKeyedRoute('extend');
	}

	public function getRegenerateTokenUrl() : string
	{
		return $this->getKeyedRoute('regenerateToken');
	}

	public function getDuplicateUrl() : string
	{
		return $this->getKeyedRoute('duplicate');
	}

	public function getPreviewUrl() : string
	{
		return $this->getKeyedRoute('preview');
	}

	public function getRevokeButton() : Button
	{
		return Button::create([
			'href' => $this->getRevokeUrl(),
			'text' => trans('temporarylinks::actions.revoke'),
			'icon' => 'ban',
		]);
	}

	public function getRegenerateTokenButton() : Button
	{
		return Button::create([
			'href' => $this->getRegenerateTokenUrl(),
			'text' => trans('temporarylinks::actions.regenerateToken'),
			'icon' => 'refresh',
		]);
	}

	public function getDuplicateButton() : Button
	{
		return Button::create([
			'href' => $this->getDuplicateUrl(),
			'text' => trans('temporarylinks::actions.duplicate'),
			'icon' => 'copy',
		]);
	}

	public function getPreviewButton() : Button
	{
		return Button::create([
			'href' => $this->getPreviewUrl(),
			'text' => trans('temporarylinks::actions.preview'),
			'icon' => 'link',
		]);
	}
}
