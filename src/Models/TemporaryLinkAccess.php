<?php

namespace IlBronza\TemporaryLinks\Models;

use IlBronza\CRUD\Models\BaseModel;
use IlBronza\CRUD\Traits\Model\PackagedModelsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemporaryLinkAccess extends BaseModel
{
	use PackagedModelsTrait;

	const RESULT_ALLOWED = 'allowed';
	const RESULT_BLOCKED = 'blocked';

	const REASON_NOT_FOUND = 'not_found';
	const REASON_NOT_STARTED = 'not_started';
	const REASON_EXPIRED = 'expired';
	const REASON_REVOKED = 'revoked';
	const REASON_CONSUMED = 'consumed';
	const REASON_VISIT_LIMIT_REACHED = 'visit_limit_reached';
	const REASON_PASSWORD_REQUIRED = 'password_required';
	const REASON_PASSWORD_FAILED = 'password_failed';
	const REASON_INVALID_DESTINATION = 'invalid_destination';

	static $packageConfigPrefix = 'temporarylinks';
	static $modelConfigPrefix = 'temporaryLinkAccess';

	public ?string $translationFolderPrefix = 'temporarylinks';

	protected $guarded = ['id'];

	protected $casts = [
		'accessed_at' => 'datetime',
		'payload' => 'array',
	];

	public function temporaryLink() : BelongsTo
	{
		return $this->belongsTo(TemporaryLink::gpc(), 'temporary_link_id');
	}
}
