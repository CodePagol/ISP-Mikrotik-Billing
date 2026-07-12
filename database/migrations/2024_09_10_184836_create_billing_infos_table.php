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
        Schema::create('billing_infos', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('customer_id')->nullable()->constrained('customers_infos')->cascadeOnUpdate()->nullOnDelete();
            // $table->foreignId('customer_infos_id')->constrained('customers_infos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('customer_bill_unique_id');
            $table->foreign('customer_bill_unique_id')->references('customer_unique_id')->on('customers_infos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('monthly_rent', 11, 2)->default(0.00);
            $table->decimal('additional_charge', 11, 2)->default(0.00);
            $table->decimal('vat', 11, 2)->default(0.00);
            $table->decimal('previous_due', 11, 2)->default(0.00);

            $table->decimal('discount', 11, 2)->default(0.00);
            $table->decimal('advance', 11, 2)->default(0.00);

            $table->decimal('total_amount', 11, 2)->default(0.00);
            $table->decimal('paid_amount', 11, 2)->default(0.00);
            $table->timestamp('paid_date')->nullable();

            $table->decimal('due_amount', 11, 2)->default(0.00);

            $table->enum('billing_type', ['prepaid', 'postpaid']);

            $table->boolean('auto_disable')->default(true);
            $table->timestamp('auto_disable_date')->nullable();
            $table->tinyInteger('auto_disable_month')->nullable();
            $table->timestamp('extra_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_infos');
    }
};
