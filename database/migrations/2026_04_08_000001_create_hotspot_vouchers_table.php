<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('router_name');
            $table->string('code')->unique();
            $table->string('profile');
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('batch_name')->nullable();
            $table->enum('status', ['unused', 'used', 'expired'])->default('unused');
            $table->string('used_by')->nullable();
            $table->string('mac_address')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('comment')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['router_name', 'status']);
            $table->index('batch_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspot_vouchers');
    }
};
