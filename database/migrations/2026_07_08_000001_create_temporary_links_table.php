<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up() : void
	{
		Schema::create(config('temporarylinks.models.temporaryLink.table'), function (Blueprint $table)
		{
			$table->uuid('id')->primary();

			$table->string('name');
			$table->text('description')->nullable();

			$table->string('token_hash', 64)->unique();

			$table->string('destination_type', 16);
			$table->string('destination_route')->nullable();
			$table->json('destination_parameters')->nullable();
			$table->text('destination_url')->nullable();

			$table->timestamp('starts_at')->nullable();
			$table->timestamp('expires_at')->nullable();

			$table->timestamp('revoked_at')->nullable();
			$table->unsignedBigInteger('revoked_by')->nullable();
			$table->foreign('revoked_by')->references('id')->on('users');

			$table->timestamp('consumed_at')->nullable();

			$table->unsignedInteger('max_visits')->nullable();
			$table->unsignedInteger('visits_count')->default(0);

			$table->string('password_hash')->nullable();

			$table->boolean('consume_on_first_success')->default(false);

			$table->string('subject_type')->nullable();
			$table->string('subject_id')->nullable();
			$table->index(['subject_type', 'subject_id']);

			$table->unsignedBigInteger('created_by')->nullable();
			$table->foreign('created_by')->references('id')->on('users');

			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down() : void
	{
		Schema::dropIfExists(config('temporarylinks.models.temporaryLink.table'));
	}
};
