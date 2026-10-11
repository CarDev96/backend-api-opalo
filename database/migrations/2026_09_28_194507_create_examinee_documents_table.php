<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examinee_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('examinee_id')->constrained('examinees')->cascadeOnDelete();

            // candidate_photo, house_facade, house_living_room, house_kitchen
            $table->string('document_type', 50);
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type', 50)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            // Estatus de revisión y motivo de rechazo
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
            $table->softDeletes(); // 👈 SoftDeletes

            // Una foto por tipo por evaluado
            $table->unique(['examinee_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examinee_documents');
    }
};
