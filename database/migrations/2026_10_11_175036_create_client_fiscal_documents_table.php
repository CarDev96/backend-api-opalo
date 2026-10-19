<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_fiscal_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('clients')->cascadeOnDelete();

            // Datos fiscales capturados o confirmados
            $table->string('rfc', 13);
            $table->string('business_name', 200); // Razón social
            $table->string('fiscal_regime', 150); // Régimen fiscal
            $table->string('postal_code', 10);

            // Archivo PDF de la Constancia en S3
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->default('application/pdf');
            $table->unsignedBigInteger('file_size');

            // Estatus de revisión fiscal
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_fiscal_documents');
    }
};
