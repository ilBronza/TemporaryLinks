<?php

namespace IlBronza\TemporaryLinks\Console\Commands;

use IlBronza\TemporaryLinks\Models\TemporaryLink;
use IlBronza\TemporaryLinks\Models\TemporaryLinkAccess;
use Illuminate\Console\Command;

use function config;
use function now;

class TemporaryLinksCleanupCommand extends Command
{
	protected $signature = 'temporarylinks:cleanup';

	protected $description = 'Deletes old access logs and reports expired temporary links';

	public function handle() : int
	{
		$retentionDays = (int) config('temporarylinks.cleanup.access_log_retention_days');

		$deleted = TemporaryLinkAccess::gpc()::where(
			'accessed_at', '<', now()->subDays($retentionDays)
		)->delete();

		$this->info("Deleted {$deleted} access logs older than {$retentionDays} days");

		$expired = TemporaryLink::gpc()::expired()->count();

		$this->info("Expired links still stored: {$expired}");

		return self::SUCCESS;
	}
}
