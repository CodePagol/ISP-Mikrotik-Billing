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
        Schema::table('package_lists', function (Blueprint $table) {
            $table->unsignedBigInteger('router_id')->nullable()->after('id');
            $table->foreign('router_id')->references('id')->on('router_lists')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_lists', function (Blueprint $table) {
            $table->dropForeign(['router_id']);
            $table->dropColumn('router_id');
        });
    }
};
