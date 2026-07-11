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
        Schema::create('collection_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('customer_collection_unique_id');
            // $table->foreign('customer_collection_unique_id')->references('customer_unique_id')->on('customers_infos')->cascadeOnUpdate();
            $table->timestamp('collection_date');
            $table->decimal('collection_amount', 11, 2)->default(0.00);
            $table->string('collected_by')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('payment_status')->nullable();
            $table->bigInteger('invoice_no')->unique();
            $table->string('bill_month')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_summaries');
    }
};
