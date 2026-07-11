<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mikrotik_logs', function (Blueprint $table) {
            $table->id();
            $table->string('router_name')->index();
            $table->string('log_id')->nullable();          // .id from MikroTik
            $table->string('time')->nullable();            // MikroTik time string
            $table->string('buffer')->nullable();
            $table->string('topics')->nullable();          // e.g. "info,account"
            $table->text('message');         // MikroTik buffer name (e.g. memory, disk)
            $table->timestamps();

            $table->index(['router_name', 'created_at']);
        });

        // Add log_server settings to site_settings
        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (! Schema::hasColumn('site_settings', 'log_server_enabled')) {
                    $table->boolean('log_server_enabled')->default(false)->after('site_secret_email');
                }
                if (! Schema::hasColumn('site_settings', 'log_server_routers')) {
                    $table->text('log_server_routers')->nullable()->after('log_server_enabled');
                }
                if (! Schema::hasColumn('site_settings', 'log_retention_days')) {
                    $table->integer('log_retention_days')->default(30)->after('log_server_routers');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mikrotik_logs');

        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                foreach (['log_server_enabled', 'log_server_routers', 'log_retention_days'] as $col) {
                    if (Schema::hasColumn('site_settings', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
