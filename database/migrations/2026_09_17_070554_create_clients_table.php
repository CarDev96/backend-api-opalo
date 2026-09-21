<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('company_name');                    // Razón Social
            $table->string('trade_name')->nullable();           // Nombre Comercial
            $table->string('rfc', 13)->nullable()->unique();
            $table->string('contact_email');
            $table->string('contact_phone', 20)->nullable();
            $table->string('fiscal_regime')->nullable();
            $table->string('fiscal_zip_code', 10)->nullable();
            $table->string('tax_status_certificate')->nullable(); // Ruta archivo CSF (PDF)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
