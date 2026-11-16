<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('psychometric_test_files', function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->uuid('psychometric_test_id');
			$table->string('file_path');
			$table->string('original_name');
			$table->string('mime_type', 50)->nullable();
			$table->unsignedBigInteger('file_size')->nullable();
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('psychometric_test_id')
				->references('id')
				->on('psychometric_tests')
				->onDelete('cascade');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('psychometric_test_files');
	}
};
