<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_types', function (Blueprint $table) {
            if (!Schema::hasColumn('evaluation_types', 'cost')) {
                $table->decimal('cost', 10, 2)->default(0)->after('description');
            }
            if (!Schema::hasColumn('evaluation_types', 'includes_vat')) {
                $table->boolean('includes_vat')->default(false)->after('cost');
            }
            if (!Schema::hasColumn('evaluation_types', 'modality')) {
                $table->string('modality', 50)->default('Presencial')->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_types', function (Blueprint $table) {
            $table->dropColumn(['cost', 'includes_vat', 'modality']);
        });
    }
};
