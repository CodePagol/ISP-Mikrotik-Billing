<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('isp_expenses', function (Blueprint $table) {
            // Change category column to string to support reseller payout and other categories dynamically
            $table->string('category', 100)->change();

            // Link to a system user (e.g. employee salary recipient)
            $table->unsignedBigInteger('linked_user_id')->nullable()->after('added_by');
            $table->foreign('linked_user_id')->references('id')->on('users')->nullOnDelete();

            // Link to a reseller (e.g. commission payout)
            $table->unsignedBigInteger('linked_reseller_id')->nullable()->after('linked_user_id');
            $table->foreign('linked_reseller_id')->references('id')->on('resellers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('isp_expenses', function (Blueprint $table) {
            $table->dropForeign(['linked_user_id']);
            $table->dropForeign(['linked_reseller_id']);
            $table->dropColumn(['linked_user_id', 'linked_reseller_id']);

            // Revert category to enum
            $table->enum('category', ['item_purchase', 'raw_bill', 'employee_salary', 'miscellaneous'])->change();
        });
    }
};
