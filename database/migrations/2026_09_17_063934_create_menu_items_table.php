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
		Schema::create('menu_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('menu_section_id')->constrained('menu_sections')->cascadeOnDelete();
            $table->string('title');                     // 'Usuarios', 'Clientes', 'Citas', etc.
            $table->string('route');                     // '/users', '/appointments', etc.
            $table->string('icon')->nullable();          // 'mdi-calendar-clock-outline'
            $table->integer('order_index')->default(1);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
