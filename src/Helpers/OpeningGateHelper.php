<?php

namespace IlBronza\TemporaryLinks\Helpers;

use IlBronza\TemporaryLinks\DataTransferObjects\OpeningResult;
use IlBronza\TemporaryLinks\Models\TemporaryLink;
use IlBronza\TemporaryLinks\Models\TemporaryLinkAccess;

class OpeningGateHelper
{
	static function check(TemporaryLink $link) : OpeningResult
	{
		if($link->isRevoked())
			return OpeningResult::block(TemporaryLinkAccess::REASON_REVOKED);

		if($link->isConsumed())
			return OpeningResult::block(TemporaryLinkAccess::REASON_CONSUMED);

		if($link->isNotStarted())
			return OpeningResult::block(TemporaryLinkAccess::REASON_NOT_STARTED);

		if($link->isExpired())
			return OpeningResult::block(TemporaryLinkAccess::REASON_EXPIRED);

		if($link->hasReachedVisitLimit())
			return OpeningResult::block(TemporaryLinkAccess::REASON_VISIT_LIMIT_REACHED);

		if(! DestinationHelper::isValid($link))
			return OpeningResult::block(TemporaryLinkAccess::REASON_INVALID_DESTINATION);

		return OpeningResult::allow();
	}
}
