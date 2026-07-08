<?php

namespace IlBronza\TemporaryLinks\Models;

use Carbon\Carbon;
use IlBronza\CRUD\Models\BaseModel;
use IlBronza\CRUD\Traits\Model\CRUDUseUuidTrait;
use IlBronza\CRUD\Traits\Model\PackagedModelsTrait;
use IlBronza\TemporaryLinks\DataTransferObjects\CreatedLinkResult;
use IlBronza\TemporaryLinks\DataTransferObjects\OpeningResult;
use IlBronza\TemporaryLinks\Events\TemporaryLinkConsumed;
use IlBronza\TemporaryLinks\Events\TemporaryLinkCreated;
use IlBronza\TemporaryLinks\Events\TemporaryLinkRevoked;
use IlBronza\TemporaryLinks\Helpers\DestinationHelper;
use IlBronza\TemporaryLinks\Helpers\OpeningGateHelper;
use IlBronza\TemporaryLinks\Helpers\TokenHelper;
use IlBronza\TemporaryLinks\Models\Traits\TemporaryLinkButtonsRoutesTrait;
use IlBronza\TemporaryLinks\Models\Traits\TemporaryLinkStatusTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use function config;
use function event;
use function now;

class TemporaryLink extends BaseModel
{
	use PackagedModelsTrait;
	use CRUDUseUuidTrait;
	use TemporaryLinkStatusTrait;
	use TemporaryLinkButtonsRoutesTrait;

	const DESTINATION_TYPE_ROUTE = 'route';
	const DESTINATION_TYPE_URL = 'url';

	const STATUS_DRAFT = 'draft';
	const STATUS_SCHEDULED = 'scheduled';
	const STATUS_ACTIVE = 'active';
	const STATUS_EXPIRED = 'expired';
	const STATUS_REVOKED = 'revoked';
	const STATUS_CONSUMED = 'consumed';
	const STATUS_LIMIT_REACHED = 'limit_reached';

	static $packageConfigPrefix = 'temporarylinks';
	static $modelConfigPrefix = 'temporaryLink';

	public ?string $translationFolderPrefix = 'temporarylinks';

	protected $keyType = 'string';

	protected $guarded = ['id', 'token_hash', 'password_hash', 'visits_count', 'revoked_at', 'revoked_by', 'consumed_at'];

	public ?string $lastGeneratedPlainToken = null;

	protected $casts = [
		'destination_parameters' => 'array',
		'starts_at' => 'datetime',
		'expires_at' => 'datetime',
		'revoked_at' => 'datetime',
		'consumed_at' => 'datetime',
		'consume_on_first_success' => 'boolean',
		'deleted_at' => 'datetime',
	];

	protected static function booted() : void
	{
		static::creating(function (TemporaryLink $link)
		{
			if(! $link->token_hash)
				$link->applyNewToken();

			if(! $link->created_by)
				$link->created_by = Auth::id();
		});

		static::created(function (TemporaryLink $link)
		{
			event(new TemporaryLinkCreated($link));
		});
	}

	static function getUserClass() : string
	{
		return config('auth.providers.users.model');
	}

	public function accesses() : HasMany
	{
		return $this->hasMany(TemporaryLinkAccess::gpc(), 'temporary_link_id');
	}

	public function subject() : MorphTo
	{
		return $this->morphTo();
	}

	public function creator() : BelongsTo
	{
		return $this->belongsTo(static::getUserClass(), 'created_by');
	}

	public function revoker() : BelongsTo
	{
		return $this->belongsTo(static::getUserClass(), 'revoked_by');
	}

	static function findByPlainToken(string $plainToken) : ?static
	{
		return static::gpc()::where(
			'token_hash',
			TokenHelper::hash($plainToken)
		)->first();
	}

	public function applyNewToken() : string
	{
		$plainToken = TokenHelper::generate();

		$this->token_hash = TokenHelper::hash($plainToken);
		$this->lastGeneratedPlainToken = $plainToken;

		return $plainToken;
	}

	public function regenerateToken() : string
	{
		$plainToken = $this->applyNewToken();

		$this->save();

		return $plainToken;
	}

	public function getPublicUrl(string $plainToken) : string
	{
		return TokenHelper::buildPublicUrl($plainToken);
	}

	public function setDestinationParametersAttribute($value) : void
	{
		if(is_string($value))
			$value = json_decode($value, true);

		$this->attributes['destination_parameters'] = $value ? json_encode($value) : null;
	}

	public function setPasswordAttribute(?string $plainPassword) : void
	{
		$this->password_hash = $plainPassword ? Hash::make($plainPassword) : null;
	}

	public function requiresPassword() : bool
	{
		return ! is_null($this->password_hash);
	}

	public function checkPassword(string $plainPassword) : bool
	{
		return Hash::check($plainPassword, $this->password_hash);
	}

	public function requiresInterstitial() : bool
	{
		if($this->consume_on_first_success)
			return true;

		return ! is_null($this->max_visits);
	}

	public function canBeOpened() : OpeningResult
	{
		return OpeningGateHelper::check($this);
	}

	public function resolveDestinationUrl() : string
	{
		return DestinationHelper::resolve($this);
	}

	public function markVisited() : bool
	{
		$query = static::query()->whereKey($this->getKey());

		if($this->max_visits)
			$query->where('visits_count', '<', $this->max_visits);

		if(! $query->increment('visits_count'))
			return false;

		$this->refresh();

		return true;
	}

	public function revoke(?User $user = null) : static
	{
		$this->revoked_at = now();
		$this->revoked_by = $user?->getKey() ?? Auth::id();

		$this->save();

		event(new TemporaryLinkRevoked($this));

		return $this;
	}

	public function reactivate() : static
	{
		$this->revoked_at = null;
		$this->revoked_by = null;

		$this->save();

		return $this;
	}

	public function consume() : static
	{
		$this->consumed_at = now();

		$this->save();

		event(new TemporaryLinkConsumed($this));

		return $this;
	}

	public function extend(Carbon $expiresAt) : static
	{
		$this->expires_at = $expiresAt;

		$this->save();

		return $this;
	}

	public function duplicate() : CreatedLinkResult
	{
		$copy = $this->replicate([
			'token_hash',
			'visits_count',
			'revoked_at',
			'revoked_by',
			'consumed_at',
		]);

		$copy->visits_count = 0;

		$plainToken = $copy->applyNewToken();

		$copy->save();

		return new CreatedLinkResult($copy, $plainToken);
	}
}
