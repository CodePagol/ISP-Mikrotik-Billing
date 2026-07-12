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
        Schema::create('p_p_p_secrets', function (Blueprint $table) {
            $table->id();
            $table->string('router_name')->nullable();
            $table->foreign('router_name')->references('router_name')->on('router_lists')->cascadeOnUpdate();
            $table->string('username')->nullable();
            $table->string('password')->default('-');
            $table->rememberToken();
            $table->string('service')->default('-');
            $table->string('profile')->default('-');
            $table->string('caller_id')->nullable();
            $table->string('comment')->nullable();
            $table->string('ppp_remote_ip')->nullable();
            $table->string('bandwidth')->nullable();
            $table->timestamp('uptime')->nullable();
            $table->timestamp('downtime')->nullable();
            $table->timestamp('last_logged_out')->nullable();
            $table->string('last_caller_id')->nullable();
            $table->string('last_disconnect_reason')->nullable();
            $table->string('routes')->nullable();
            $table->string('ipv6_routes')->nullable();
            $table->string('status')->default('pending');
            // $table->string('package_name')->nullable();
            // $table->foreign('package_name')->references('package_name')->on('package_lists')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p_p_p_secrets');
    }
};
