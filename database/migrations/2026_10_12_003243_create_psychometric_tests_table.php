<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psychometric_tests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Datos del catálogo
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            // Archivo EN BLANCO que sube el administrador
            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type', 50)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psychometric_tests');
    }
};
