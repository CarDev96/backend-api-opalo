<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignUuid('examinee_id')->constrained('examinees')->cascadeOnDelete();
            $table->foreignUuid('psychologist_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('evaluation_type_id')->constrained('evaluation_types')->cascadeOnDelete();

            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('modality', ['presencial', 'remota'])->default('presencial');
            $table->enum('status', ['scheduled', 'attended', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Regla de oro: un psicólogo no puede tener dos citas empalmadas al mismo tiempo
            $table->unique(['psychologist_id', 'date', 'start_time'], 'unique_psychologist_slot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
