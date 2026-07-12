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
        Schema::create('router_lists', function (Blueprint $table) {
            $table->id();
            $table->string('router_name')->unique();
            $table->string('ip_address');
            $table->string('username');
            $table->string('password');
            $table->integer('ssh_port')->nullable();
            $table->integer('api_port')->nullable();
            $table->string('action')->default('connected');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('router_lists');
    }
};
