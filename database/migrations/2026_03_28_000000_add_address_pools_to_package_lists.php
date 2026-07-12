<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_lists', function (Blueprint $table) {
            $table->string('mikrotik_local_address')->nullable()->after('mikrotik_rate_limit');
            $table->string('mikrotik_remote_address')->nullable()->after('mikrotik_local_address');
        });
    }

    public function down(): void
    {
        Schema::table('package_lists', function (Blueprint $table) {
            $table->dropColumn(['mikrotik_local_address', 'mikrotik_remote_address']);
        });
    }
};
