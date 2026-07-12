<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_sales', function (Blueprint $table) {
            $table->id();
            $table->string('router_name');
            $table->string('voucher_code')->nullable();
            $table->string('profile');
            $table->string('username');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('payment_method')->default('cash');
            $table->string('note')->nullable();
            $table->date('sale_date');
            $table->foreignId('sold_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['router_name', 'sale_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspot_sales');
    }
};
