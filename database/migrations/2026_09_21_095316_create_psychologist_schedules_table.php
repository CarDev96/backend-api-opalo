<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psychologist_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();

            // 1 = Lunes, 2 = Martes, 3 = Miércoles, 4 = Jueves, 5 = Viernes, 6 = Sábado
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time'); // ej. 08:00:00
            $table->time('end_time');   // ej. 17:00:00
            $table->unsignedSmallInteger('slot_duration_minutes')->default(60); // Bloques de 1 hora
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Un psicólogo solo tiene una franja configurada por cada día de la semana
            $table->unique(['user_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psychologist_schedules');
    }
};
