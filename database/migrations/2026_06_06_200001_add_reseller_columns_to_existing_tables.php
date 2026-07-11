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
        // Add reseller_id to customers_infos table
        Schema::table('customers_infos', function (Blueprint $table) {
            $table->foreignId('reseller_id')->nullable()->after('id')->constrained('resellers')->nullOnDelete();
        });

        // Add reseller_id to package_lists table
        Schema::table('package_lists', function (Blueprint $table) {
            $table->foreignId('reseller_id')->nullable()->after('id')->constrained('resellers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_lists', function (Blueprint $table) {
            $table->dropForeign(['reseller_id']);
            $table->dropColumn('reseller_id');
        });

        Schema::table('customers_infos', function (Blueprint $table) {
            $table->dropForeign(['reseller_id']);
            $table->dropColumn('reseller_id');
        });
    }
};
