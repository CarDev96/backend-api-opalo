<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_payment_files', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relación con el Cliente
            $table->uuid('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();

            // Relación directa con la Cuenta Bancaria receptora
            $table->uuid('bank_account_id');
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->restrictOnDelete();

            // Datos físicos del archivo en S3 / MinIO
            $table->string('title');             // Nombre original del archivo (ej. comprobante.pdf)
            $table->string('mime');              // application/pdf, image/jpeg, etc.
            $table->string('extension');         // pdf, jpg, png
            $table->string('path');              // clients/{client_id}/payments/{file_id}/

            // Metadatos contables y de auditoría
            $table->decimal('amount', 12, 2)->nullable();
            $table->enum('account_type', ['fiscal', 'non_fiscal'])->default('fiscal');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_payment_files');
    }
};
