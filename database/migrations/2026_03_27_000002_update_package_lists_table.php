<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_lists', function (Blueprint $table) {
            $table->string('plan_label')->nullable()->after('package'); // e.g. MINOR, JUNIOR, BASIC
            $table->string('speed')->nullable()->after('plan_label');   // e.g. 8 Mbps
            $table->json('features')->nullable()->after('speed');       // ["24 HOURS UNLIMITED", "Fiber Optics"]
            $table->boolean('is_featured')->default(false)->after('features');
            $table->boolean('show_on_site')->default(true)->after('is_featured');
            $table->unsignedInteger('sort_order')->default(0)->after('show_on_site');
        });
    }

    public function down(): void
    {
        Schema::table('package_lists', function (Blueprint $table) {
            $table->dropColumn(['plan_label', 'speed', 'features', 'is_featured', 'show_on_site', 'sort_order']);
        });
    }
};
