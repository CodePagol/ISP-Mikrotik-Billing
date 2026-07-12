<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collection_summaries', function (Blueprint $table) {
            if (! Schema::hasColumn('collection_summaries', 'invoice_no')) {
                $table->bigInteger('invoice_no')->nullable()->unique()->after('payment_status');
            }
            if (! Schema::hasColumn('collection_summaries', 'bill_month')) {
                $table->string('bill_month')->nullable()->after('invoice_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collection_summaries', function (Blueprint $table) {
            if (Schema::hasColumn('collection_summaries', 'invoice_no')) {
                $table->dropColumn('invoice_no');
            }
            if (Schema::hasColumn('collection_summaries', 'bill_month')) {
                $table->dropColumn('bill_month');
            }
        });
    }
};
