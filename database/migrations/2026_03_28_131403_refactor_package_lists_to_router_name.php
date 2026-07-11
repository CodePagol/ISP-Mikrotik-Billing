<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Drop foreign key in customers_infos so we can modify package_lists unique index
        Schema::table('customers_infos', function (Blueprint $table) {
            $table->dropForeign(['package_name']);
        });

        // 2. Add router_name string column to package_lists
        Schema::table('package_lists', function (Blueprint $table) {
            $table->string('router_name')->nullable()->after('id');
        });

        // 3. Backfill router_name from router_id if data exists
        $packages = DB::table('package_lists')->get();
        foreach ($packages as $pkg) {
            if ($pkg->router_id) {
                $router = DB::table('router_lists')->where('id', $pkg->router_id)->first();
                if ($router) {
                    DB::table('package_lists')->where('id', $pkg->id)->update(['router_name' => $router->router_name]);
                }
            }
        }

        // 4. Drop router_id and its foreign key
        Schema::table('package_lists', function (Blueprint $table) {
            $table->dropForeign(['router_id']);
            $table->dropColumn('router_id');
        });

        // 5. Drop global unique index on package and add composite unique
        Schema::table('package_lists', function (Blueprint $table) {
            // Drop naming can vary, usually [table]_[column]_unique
            $table->dropUnique('package_lists_package_unique');
            $table->unique(['package', 'router_name'], 'package_lists_package_router_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_lists', function (Blueprint $table) {
            $table->dropUnique('package_lists_package_router_name_unique');
            $table->unique('package', 'package_lists_package_unique');
            $table->unsignedBigInteger('router_id')->nullable()->after('id');
            $table->foreign('router_id')->references('id')->on('router_lists')->onDelete('cascade');
        });

        Schema::table('customers_infos', function (Blueprint $table) {
            $table->foreign('package_name')->references('package')->on('package_lists')->cascadeOnUpdate()->nullOnDelete();
        });
    }
};
