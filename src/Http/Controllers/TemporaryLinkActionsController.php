<?php

namespace IlBronza\TemporaryLinks\Http\Controllers;

use IlBronza\TemporaryLinks\Models\TemporaryLink;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use function back;
use function redirect;
use function session;
use function trans;

class TemporaryLinkActionsController extends Controller
{
	public function revoke(TemporaryLink $temporaryLink)
	{
		$temporaryLink->revoke();

		return back()->with('message', trans('temporarylinks::messages.linkRevoked'));
	}

	public function reactivate(TemporaryLink $temporaryLink)
	{
		$temporaryLink->reactivate();

		return back()->with('message', trans('temporarylinks::messages.linkReactivated'));
	}

	public function extend(Request $request, TemporaryLink $temporaryLink)
	{
		$validated = $request->validate([
			'expires_at' => 'required|date|after:now',
		]);

		$temporaryLink->extend(
			\Carbon\Carbon::parse($validated['expires_at'])
		);

		return back()->with('message', trans('temporarylinks::messages.linkExtended', [
			'date' => $temporaryLink->expires_at->format('d/m/Y H:i'),
		]));
	}

	public function regenerateToken(TemporaryLink $temporaryLink)
	{
		$plainToken = $temporaryLink->regenerateToken();

		$this->flashPlainUrl($temporaryLink, $plainToken);

		return back();
	}

	public function duplicate(TemporaryLink $temporaryLink)
	{
		$result = $temporaryLink->duplicate();

		$this->flashPlainUrl($result->link, $result->plainToken);

		return redirect()->to($result->link->getEditUrl());
	}

	public function preview(TemporaryLink $temporaryLink)
	{
		return redirect()->away(
			$temporaryLink->resolveDestinationUrl()
		);
	}

	private function flashPlainUrl(TemporaryLink $temporaryLink, string $plainToken) : void
	{
		$url = $temporaryLink->getPublicUrl($plainToken);

		session()->flash('temporarylinks.plainUrl', $url);

		session()->flash('message', trans('temporarylinks::messages.tokenRegenerated', [
			'url' => $url,
		]));
	}
}
