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
        Schema::create('address_fields', function (Blueprint $table) {
            $table->id();
            $table->string('label')->unique(); // Field label like 'District', 'Post Office'
            $table->enum('input_type', ['text', 'dropdown', 'textarea']); // Input type
            $table->text('dropdown_list')->nullable(); // Dropdown list
            $table->boolean('required')->default(false); // If field is required or not
            $table->boolean('print_preview')->default(false); // If field is required or not
            $table->boolean('complain_preview')->default(false); // If field is required or not
            $table->integer('order')->default(0); // Order for sorting
            $table->integer('receipt_order')->default(0); // Order for sorting
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address_fields');
    }
};
