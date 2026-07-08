<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up() : void
	{
		Schema::create(config('temporarylinks.models.temporaryLinkAccess.table'), function (Blueprint $table)
		{
			$table->id();

			$table->uuid('temporary_link_id')->nullable();
			$table->foreign('temporary_link_id')->references('id')->on(config('temporarylinks.models.temporaryLink.table'));

			$table->timestamp('accessed_at');

			$table->string('ip', 45)->nullable();
			$table->text('user_agent')->nullable();

			$table->string('result', 16);
			$table->string('failure_reason', 32)->nullable();

			$table->string('redirected_to')->nullable();

			$table->json('payload')->nullable();

			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down() : void
	{
		Schema::dropIfExists(config('temporarylinks.models.temporaryLinkAccess.table'));
	}
};
