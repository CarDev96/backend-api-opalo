<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examinee_documents', function (Blueprint $table) {
            // 1. Crear un índice simple en examinee_id para respaldar la foreign key
            $table->index('examinee_id', 'examinee_documents_examinee_id_index');

            // 2. Ahora MySQL permite eliminar el índice único compuesto sin conflicto
            $table->dropUnique('examinee_documents_examinee_id_document_type_unique');
        });
    }

    public function down(): void
    {
        Schema::table('examinee_documents', function (Blueprint $table) {
            // Revertir: restaurar el índice único y eliminar el índice auxiliar
            $table->unique(['examinee_id', 'document_type'], 'examinee_documents_examinee_id_document_type_unique');
            $table->dropIndex('examinee_documents_examinee_id_index');
        });
    }
};
