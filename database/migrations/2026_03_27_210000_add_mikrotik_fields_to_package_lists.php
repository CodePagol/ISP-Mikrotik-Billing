<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_lists', function (Blueprint $table) {
            $table->string('mikrotik_rate_limit')->nullable()->after('speed'); // e.g. 8M/8M
            $table->boolean('push_to_mikrotik')->default(false)->after('mikrotik_rate_limit');
        });
    }

    public function down(): void
    {
        Schema::table('package_lists', function (Blueprint $table) {
            $table->dropColumn(['mikrotik_rate_limit', 'push_to_mikrotik']);
        });
    }
};
