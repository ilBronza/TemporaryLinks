<?php

namespace IlBronza\TemporaryLinks\Helpers;

use Carbon\Carbon;
use IlBronza\TemporaryLinks\DataTransferObjects\CreatedLinkResult;
use IlBronza\TemporaryLinks\Models\TemporaryLink;
use Illuminate\Database\Eloquent\Model;

use function config;
use function now;

class TemporaryLinkBuilder
{
	protected TemporaryLink $link;

	protected function __construct()
	{
		$this->link = TemporaryLink::gpc()::make();

		$this->link->expires_at = now()->addMinutes(
			(int) config('temporarylinks.default_expires_in_minutes')
		);
	}

	static function create() : static
	{
		return new static();
	}

	public function name(string $name) : static
	{
		$this->link->name = $name;

		return $this;
	}

	public function description(string $description) : static
	{
		$this->link->description = $description;

		return $this;
	}

	public function route(string $routeName, array $parameters = []) : static
	{
		$this->link->destination_type = TemporaryLink::DESTINATION_TYPE_ROUTE;
		$this->link->destination_route = $routeName;
		$this->link->destination_parameters = $parameters;

		return $this;
	}

	public function url(string $url) : static
	{
		$this->link->destination_type = TemporaryLink::DESTINATION_TYPE_URL;
		$this->link->destination_url = $url;

		return $this;
	}

	public function for(Model $subject) : static
	{
		$this->link->subject()->associate($subject);

		return $this;
	}

	public function startsAt(Carbon $startsAt) : static
	{
		$this->link->starts_at = $startsAt;

		return $this;
	}

	public function expiresAt(?Carbon $expiresAt) : static
	{
		$this->link->expires_at = $expiresAt;

		return $this;
	}

	public function expiresInMinutes(int $minutes) : static
	{
		return $this->expiresAt(now()->addMinutes($minutes));
	}

	public function neverExpires() : static
	{
		return $this->expiresAt(null);
	}

	public function maxVisits(int $maxVisits) : static
	{
		$this->link->max_visits = $maxVisits;

		return $this;
	}

	public function password(string $plainPassword) : static
	{
		$this->link->password = $plainPassword;

		return $this;
	}

	public function consumeOnFirstSuccess(bool $consume = true) : static
	{
		$this->link->consume_on_first_success = $consume;

		return $this;
	}

	public function save() : CreatedLinkResult
	{
		$plainToken = $this->link->applyNewToken();

		$this->link->save();

		return new CreatedLinkResult($this->link, $plainToken);
	}
}
