<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Tipo de cuenta: fiscal (con factura) o non_fiscal (sin factura)
            $table->enum('type', ['fiscal', 'non_fiscal'])->unique();

            $table->string('bank_name');               // Ej. BBVA México, Santander
            $table->string('account_number')->nullable(); // Número de cuenta / tarjeta opcional
            $table->string('clabe', 18);               // 18 dígitos estrictos
            $table->string('beneficiary');            // Razón social o persona física receptora
            $table->string('rfc', 13)->nullable();     // RFC emisor para depósitos fiscales
            $table->text('instructions')->nullable();  // Notas adicionales (ej. "Enviar comprobante")
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
