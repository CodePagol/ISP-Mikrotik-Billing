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
        // 1. Add package_id to customers_infos
        Schema::table('customers_infos', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id')->nullable()->after('connection_date');
        });

        // 2. Transfer data from package_name to package_id
        $customers = DB::table('customers_infos')->get();
        foreach ($customers as $customer) {
            if ($customer->package_name) {
                // To accurately find the package, we should ideally know the router.
                // But since previously packages were globally unique by name,
                // any package with this name is technically the same or we can just pick the first.
                // Ideally, if the customer has a ppp_user_id, we can check the router name.
                $routerName = null;
                if ($customer->ppp_user_id) {
                    $pppUser = DB::table('p_p_p_secrets')->where('id', $customer->ppp_user_id)->first();
                    if ($pppUser) {
                        $routerName = $pppUser->router_name;
                    }
                }

                $query = DB::table('package_lists')->where('package', $customer->package_name);
                if ($routerName) {
                    // Try to strongly type to this router's package first
                    $routerSpecificPkg = (clone $query)->where('router_name', $routerName)->first();
                    if ($routerSpecificPkg) {
                        $package = $routerSpecificPkg;
                    } else {
                        $package = $query->first(); // fallback
                    }
                } else {
                    $package = $query->first();
                }

                if ($package) {
                    DB::table('customers_infos')
                        ->where('id', $customer->id)
                        ->update(['package_id' => $package->id]);
                }
            }
        }

        // 3. Set up the foreign key constraint and drop package_name
        Schema::table('customers_infos', function (Blueprint $table) {
            $table->foreign('package_id')->references('id')->on('package_lists')->onDelete('set null');
            $table->dropColumn('package_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers_infos', function (Blueprint $table) {
            $table->string('package_name')->nullable()->after('connection_date');
        });

        // Restore data
        $customers = DB::table('customers_infos')->get();
        foreach ($customers as $customer) {
            if ($customer->package_id) {
                $package = DB::table('package_lists')->where('id', $customer->package_id)->first();
                if ($package) {
                    DB::table('customers_infos')
                        ->where('id', $customer->id)
                        ->update(['package_name' => $package->package]);
                }
            }
        }

        Schema::table('customers_infos', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropColumn('package_id');
        });
    }
};
