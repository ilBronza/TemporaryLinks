<?php

namespace IlBronza\TemporaryLinks\Http\Controllers;

use IlBronza\TemporaryLinks\Events\TemporaryLinkBlocked;
use IlBronza\TemporaryLinks\Events\TemporaryLinkOpened;
use IlBronza\TemporaryLinks\Helpers\AccessLoggerHelper;
use IlBronza\TemporaryLinks\Helpers\SessionHelper;
use IlBronza\TemporaryLinks\Models\TemporaryLink;
use IlBronza\TemporaryLinks\Models\TemporaryLinkAccess;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use function back;
use function event;
use function redirect;
use function trans;
use function view;

class TemporaryLinkRedirectController extends Controller
{
	public function show(Request $request, string $token)
	{
		if(! $link = TemporaryLink::findByPlainToken($token))
			return $this->blockWithoutLink($request);

		$result = $link->canBeOpened();

		if($result->isBlocked())
			return $this->block($link, $request, $result->failureReason);

		if($link->requiresPassword() && ! SessionHelper::hasVerifiedPassword($link))
			return view('temporarylinks::public.password', [
				'link' => $link,
				'token' => $token,
			]);

		if($link->requiresInterstitial() && ! SessionHelper::hasConfirmedInterstitial($link))
			return view('temporarylinks::public.interstitial', [
				'link' => $link,
				'token' => $token,
			]);

		return $this->performRedirect($link, $request);
	}

	public function proceed(Request $request, string $token)
	{
		if(! $link = TemporaryLink::findByPlainToken($token))
			return $this->blockWithoutLink($request);

		$result = $link->canBeOpened();

		if($result->isBlocked())
			return $this->block($link, $request, $result->failureReason);

		SessionHelper::markInterstitialConfirmed($link);

		return redirect()->route('temporarylinks.public.show', ['token' => $token]);
	}

	public function password(Request $request, string $token)
	{
		if(! $link = TemporaryLink::findByPlainToken($token))
			return $this->blockWithoutLink($request);

		$request->validate([
			'password' => 'required|string',
		]);

		if(! $link->checkPassword($request->input('password')))
			return $this->blockPasswordAttempt($link, $request);

		SessionHelper::markPasswordVerified($link);

		return redirect()->route('temporarylinks.public.show', ['token' => $token]);
	}

	private function performRedirect(TemporaryLink $link, Request $request)
	{
		$destination = $link->resolveDestinationUrl();

		if(! $link->markVisited())
			return $this->block($link, $request, TemporaryLinkAccess::REASON_VISIT_LIMIT_REACHED);

		if($link->consume_on_first_success)
			$link->consume();

		SessionHelper::grantDestination($link, $destination);

		AccessLoggerHelper::logAllowed($link, $request, $destination);

		event(new TemporaryLinkOpened($link, $destination));

		return redirect()->away($destination);
	}

	private function blockWithoutLink(Request $request)
	{
		AccessLoggerHelper::logBlocked(null, $request, TemporaryLinkAccess::REASON_NOT_FOUND);

		event(new TemporaryLinkBlocked(null, TemporaryLinkAccess::REASON_NOT_FOUND));

		return $this->errorView(TemporaryLinkAccess::REASON_NOT_FOUND, 404);
	}

	private function block(TemporaryLink $link, Request $request, string $failureReason)
	{
		AccessLoggerHelper::logBlocked($link, $request, $failureReason);

		event(new TemporaryLinkBlocked($link, $failureReason));

		return $this->errorView($failureReason, 410);
	}

	private function blockPasswordAttempt(TemporaryLink $link, Request $request)
	{
		AccessLoggerHelper::logBlocked($link, $request, TemporaryLinkAccess::REASON_PASSWORD_FAILED);

		event(new TemporaryLinkBlocked($link, TemporaryLinkAccess::REASON_PASSWORD_FAILED));

		return back()->withErrors([
			'password' => trans('temporarylinks::messages.failures.password_failed'),
		]);
	}

	private function errorView(string $failureReason, int $httpStatus)
	{
		return response()->view('temporarylinks::public.error', [
			'failureReason' => $failureReason,
			'message' => trans("temporarylinks::messages.failures.{$failureReason}"),
		], $httpStatus);
	}
}
