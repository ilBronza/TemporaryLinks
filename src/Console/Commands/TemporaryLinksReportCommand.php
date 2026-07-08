<?php

namespace IlBronza\TemporaryLinks\Console\Commands;

use IlBronza\TemporaryLinks\Models\TemporaryLink;
use Illuminate\Console\Command;

class TemporaryLinksReportCommand extends Command
{
	protected $signature = 'temporarylinks:report';

	protected $description = 'Shows a report of temporary links by status';

	public function handle() : int
	{
		$modelClass = TemporaryLink::gpc();

		$this->table(
			['metric', 'count'],
			[
				['active', $modelClass::active()->count()],
				['scheduled', $modelClass::scheduled()->count()],
				['expired', $modelClass::expired()->count()],
				['revoked', $modelClass::revoked()->count()],
				['consumed', $modelClass::consumed()->count()],
				['never opened', $modelClass::neverOpened()->count()],
				['total', $modelClass::count()],
			]
		);

		return self::SUCCESS;
	}
}
