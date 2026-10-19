<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_payment_files', function (Blueprint $table) {
            // ID UUID que proviene directamente de FileUploaderService / S3
            $table->uuid('id')->primary();

            // Relación con la Empresa Cliente
            $table->foreignUuid('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();

            // Relación con la Orden de Evaluación (nullable por compatibilidad)
            $table->foreignUuid('evaluation_order_id')
                ->nullable()
                ->constrained('evaluation_orders')
                ->nullOnDelete();

            // Relación con la Cuenta Bancaria receptora (BBVA)
            $table->foreignUuid('bank_account_id')
                ->nullable()
                ->constrained('bank_accounts')
                ->nullOnDelete();

            // Metadatos del archivo en S3
            $table->string('title');
            $table->string('mime', 100);
            $table->string('extension', 20);
            $table->string('path', 500);

            // Datos contables y fiscales
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('account_type', 50)->default('fiscal'); // fiscal, non_fiscal
            $table->text('notes')->nullable(); // Folio SPEI o referencia bancaria

            // Estatus de revisión administrativa
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->timestamps();
            $table->softDeletes();

            // Índices de consulta rápida
            $table->index(['client_id', 'status']);
            $table->index('evaluation_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_payment_files');
    }
};
