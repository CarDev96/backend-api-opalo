<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examinees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('clients')->cascadeOnDelete();

            // Datos del evaluado solicitados al cliente
            $table->string('full_name');
            $table->string('position');         // Puesto o categoría a evaluar
            $table->string('phone');            // Teléfono obligatorio
            $table->string('email')->nullable();
            $table->string('curp', 18)->nullable();

            // Expediente y estatus
            $table->string('file_number')->nullable()->unique(); // Número de expediente para lógica posterior
            $table->enum('status', ['registered', 'scheduled', 'in_process', 'completed'])->default('registered');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examinees');
    }
};
