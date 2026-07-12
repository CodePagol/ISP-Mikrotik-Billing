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
        Schema::create('payment_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('customer_payment_unique_id');
            // $table->foreign('customer_payment_unique_id')->references('customer_unique_id')->on('customers_infos')->cascadeOnUpdate();
            // $table->foreign('customer_payment_unique_id')->references('customer_unique_id')->on('customers_infos')->cascadeOnUpdate()->cascadeOnDelete();
            // $table->string('ppp_username')->nullable();
            $table->timestamp('summary_date');
            $table->decimal('monthly_rent', 11, 2)->default(0.00);
            $table->decimal('additional_charge', 11, 2)->default(0.00);
            $table->decimal('vat', 11, 2)->default(0.00);
            $table->decimal('previous_due', 11, 2)->default(0.00);
            $table->decimal('advance', 11, 2)->default(0.00);
            $table->decimal('discount', 11, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_summaries');
    }
};
