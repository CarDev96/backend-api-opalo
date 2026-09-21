<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
		Schema::create('menu_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');                     // 'General', 'Atención y Pruebas', 'Administración'
            $table->integer('order_index')->default(1);
            $table->string('icon')->nullable();          // 'mdi-view-dashboard-outline'
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_sections');
    }
};
