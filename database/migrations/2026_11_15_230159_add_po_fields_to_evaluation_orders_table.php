<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('evaluation_orders', 'po_file_path')) {
                $table->string('po_file_path')->nullable()->after('total');
                $table->string('po_original_name')->nullable()->after('po_file_path');
                $table->string('po_status')->default('pending_po')->after('po_original_name'); // pending_po, po_under_review, po_approved, po_rejected
                $table->text('po_rejection_reason')->nullable()->after('po_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_orders', function (Blueprint $table) {
            $table->dropColumn(['po_file_path', 'po_original_name', 'po_status', 'po_rejection_reason']);
        });
    }
};
