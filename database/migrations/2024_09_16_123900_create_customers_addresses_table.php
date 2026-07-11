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
        Schema::create('customers_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('customer_address_unique_id');
            $table->foreign('customer_address_unique_id')->references('customer_unique_id')->on('customers_infos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('label_name');
            $table->foreign('label_name')->references('label')->on('address_fields')->cascadeOnUpdate()->restrictedOnDelete();
            $table->string('input_type_text')->nullable();
            $table->string('input_type_dropdown')->nullable();
            // $table->foreign('input_type_dropdown')->references('option_value')->on('address_field_options')->cascadeOnUpdate()->restrictOnDelete();
            $table->text('input_type_textarea')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
