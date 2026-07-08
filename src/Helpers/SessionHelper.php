<?php

namespace IlBronza\TemporaryLinks\Helpers;

use IlBronza\TemporaryLinks\Models\TemporaryLink;

use function session;

class SessionHelper
{
	static function getPasswordVerifiedKey(TemporaryLink $link) : string
	{
		return "temporarylinks.verified.{$link->getKey()}";
	}

	static function getInterstitialConfirmedKey(TemporaryLink $link) : string
	{
		return "temporarylinks.confirmed.{$link->getKey()}";
	}

	static function getGrantedDestinationsKey() : string
	{
		return 'temporarylinks.granted';
	}

	static function markPasswordVerified(TemporaryLink $link) : void
	{
		session()->put(static::getPasswordVerifiedKey($link), true);
	}

	static function hasVerifiedPassword(TemporaryLink $link) : bool
	{
		return (bool) session(static::getPasswordVerifiedKey($link), false);
	}

	static function markInterstitialConfirmed(TemporaryLink $link) : void
	{
		session()->put(static::getInterstitialConfirmedKey($link), true);
	}

	static function hasConfirmedInterstitial(TemporaryLink $link) : bool
	{
		return (bool) session(static::getInterstitialConfirmedKey($link), false);
	}

	static function grantDestination(TemporaryLink $link, string $destinationUrl) : void
	{
		session()->put(
			static::getGrantedDestinationsKey() . ".{$link->getKey()}",
			$destinationUrl
		);
	}

	static function getGrantedDestinations() : array
	{
		return session(static::getGrantedDestinationsKey(), []);
	}
}
