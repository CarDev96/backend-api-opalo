<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('role_id')
				->nullable()
				->after('id')
				->constrained('roles')
				->nullOnDelete();

            // Si es null, es personal interno de Ópalo. Si tiene UUID, pertenece a un Cliente/Empresa
            $table->foreignUuid('client_id')
				->nullable()
				->after('role_id')
				->constrained('clients')
				->nullOnDelete();

            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['role_id']);
            $table->dropColumn(['client_id', 'role_id', 'deleted_at']);
        });
    }
};
