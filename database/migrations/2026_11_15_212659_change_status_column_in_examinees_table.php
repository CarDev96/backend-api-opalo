<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examinees', function (Blueprint $table) {
            $table->string('status', 50)->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('examinees', function (Blueprint $table) {
            // No revertir a enum para evitar pérdida de datos
        });
    }
};
