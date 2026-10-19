<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('evaluation_orders')) {
            Schema::create('evaluation_orders', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('client_id')->constrained('clients')->cascadeOnDelete();
                $table->foreignUuid('examinee_id')->constrained('examinees')->cascadeOnDelete();
                $table->foreignUuid('evaluation_type_id')->constrained('evaluation_types')->cascadeOnDelete();

                $table->decimal('subtotal', 10, 2);
                $table->decimal('vat', 10, 2);
                $table->decimal('total', 10, 2);

                $table->string('receipt_file_path')->nullable();
                $table->string('receipt_original_name')->nullable();

                $table->enum('payment_status', ['pending_receipt', 'under_review', 'approved', 'rejected'])->default('pending_receipt');
                $table->text('rejection_reason')->nullable();
                $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();

                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_orders');
    }
};
