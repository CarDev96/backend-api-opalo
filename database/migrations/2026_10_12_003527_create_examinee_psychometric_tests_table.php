<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examinee_psychometric_tests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relación con el Evaluado y la Prueba del catálogo
            $table->foreignUuid('examinee_id')->constrained('examinees')->cascadeOnDelete();
            $table->foreignUuid('psychometric_test_id')->constrained('psychometric_tests')->cascadeOnDelete();

            // Archivo RESUELTO que sube el evaluado
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type', 50)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            // Estatus de revisión y motivo de rechazo (Idéntico a examinee_documents)
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Un evaluado solo puede subir una vez la misma prueba
            $table->unique(['examinee_id', 'psychometric_test_id'], 'examinee_test_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examinee_psychometric_tests');
    }
};
