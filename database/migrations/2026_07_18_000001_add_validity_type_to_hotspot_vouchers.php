<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotspot_vouchers', function (Blueprint $table) {
            $table->string('validity_type', 20)->default('uptime')->after('comment');
            $table->string('validity_duration', 20)->nullable()->after('validity_type');
            $table->timestamp('first_login_at')->nullable()->after('validity_duration');
        });
    }

    public function down(): void
    {
        Schema::table('hotspot_vouchers', function (Blueprint $table) {
            $table->dropColumn(['validity_type', 'validity_duration', 'first_login_at']);
        });
    }
};
