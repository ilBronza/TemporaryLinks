<?php

namespace IlBronza\TemporaryLinks;

use IlBronza\CRUD\Traits\IlBronzaPackages\IlBronzaServiceProviderPackagesTrait;
use IlBronza\TemporaryLinks\Console\Commands\TemporaryLinksCleanupCommand;
use IlBronza\TemporaryLinks\Console\Commands\TemporaryLinksReportCommand;
use IlBronza\TemporaryLinks\Http\Middleware\TemporaryLinkVerifiedMiddleware;
use IlBronza\TemporaryLinks\Models\TemporaryLink;
use IlBronza\TemporaryLinks\Models\TemporaryLinkAccess;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class TemporaryLinksServiceProvider extends ServiceProvider
{
	use IlBronzaServiceProviderPackagesTrait;

	public function boot() : void
	{
		Relation::morphMap([
			'TemporaryLink' => TemporaryLink::getProjectClassName(),
			'TemporaryLinkAccess' => TemporaryLinkAccess::getProjectClassName()
		]);

		$this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'temporarylinks');
		$this->loadViewsFrom(__DIR__ . '/../resources/views', 'temporarylinks');
		$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
		$this->loadRoutesFrom(__DIR__ . '/../routes/temporarylinks.php');

		$this->app['router']->aliasMiddleware('temporarylink.verified', TemporaryLinkVerifiedMiddleware::class);

		if ($this->app->runningInConsole())
		{
			$this->bootForConsole();
		}
	}

	public function register() : void
	{
		$this->mergeConfigFrom(__DIR__ . '/../config/temporarylinks.php', 'temporarylinks');

		$this->app->singleton('temporarylinks', function ($app)
		{
			return new TemporaryLinks;
		});
	}

	public function provides()
	{
		return ['temporarylinks'];
	}

	protected function bootForConsole() : void
	{
		$this->publishes([
			__DIR__ . '/../config/temporarylinks.php' => config_path('temporarylinks.php'),
		], 'temporarylinks.config');

		$this->publishes([
			__DIR__ . '/../resources/views' => base_path('resources/views/vendor/temporarylinks'),
		], 'temporarylinks.views');

		$this->commands([
			TemporaryLinksCleanupCommand::class,
			TemporaryLinksReportCommand::class,
		]);
	}
}
