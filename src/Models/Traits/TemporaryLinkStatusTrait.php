<?php

namespace IlBronza\TemporaryLinks\Models\Traits;

use function now;

trait TemporaryLinkStatusTrait
{
	public function isRevoked() : bool
	{
		return ! is_null($this->revoked_at);
	}

	public function isConsumed() : bool
	{
		return ! is_null($this->consumed_at);
	}

	public function isNotStarted() : bool
	{
		if(! $this->starts_at)
			return false;

		return $this->starts_at->isFuture();
	}

	public function isExpired() : bool
	{
		if(! $this->expires_at)
			return false;

		return $this->expires_at->isPast();
	}

	public function hasReachedVisitLimit() : bool
	{
		if(! $this->max_visits)
			return false;

		return $this->visits_count >= $this->max_visits;
	}

	public function isDraft() : bool
	{
		return ! $this->destination_type;
	}

	public function getStatus() : string
	{
		if($this->isDraft())
			return static::STATUS_DRAFT;

		if($this->isRevoked())
			return static::STATUS_REVOKED;

		if($this->isConsumed())
			return static::STATUS_CONSUMED;

		if($this->hasReachedVisitLimit())
			return static::STATUS_LIMIT_REACHED;

		if($this->isNotStarted())
			return static::STATUS_SCHEDULED;

		if($this->isExpired())
			return static::STATUS_EXPIRED;

		return static::STATUS_ACTIVE;
	}

	public function getStatusAttribute() : string
	{
		return $this->getStatus();
	}

	public function getTranslatedStatusAttribute() : string
	{
		return trans('temporarylinks::statuses.' . $this->getStatus());
	}

	public function scopeActive($query)
	{
		return $query->whereNull('revoked_at')
			->whereNull('consumed_at')
			->where(function ($query)
			{
				$query->whereNull('starts_at')
					->orWhere('starts_at', '<=', now());
			})
			->where(function ($query)
			{
				$query->whereNull('expires_at')
					->orWhere('expires_at', '>', now());
			})
			->where(function ($query)
			{
				$query->whereNull('max_visits')
					->orWhereColumn('visits_count', '<', 'max_visits');
			});
	}

	public function scopeExpired($query)
	{
		return $query->whereNotNull('expires_at')
			->where('expires_at', '<=', now());
	}

	public function scopeRevoked($query)
	{
		return $query->whereNotNull('revoked_at');
	}

	public function scopeConsumed($query)
	{
		return $query->whereNotNull('consumed_at');
	}

	public function scopeScheduled($query)
	{
		return $query->whereNotNull('starts_at')
			->where('starts_at', '>', now());
	}

	public function scopeNeverOpened($query)
	{
		return $query->where('visits_count', 0);
	}
}
