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
        Schema::create('official_infos', function (Blueprint $table) {
            $table->id();

            // Foreign key from 'customers_infos' table
            $table->string('customer_office_unique_id');
            $table->foreign('customer_office_unique_id')
                ->references('customer_unique_id')
                ->on('customers_infos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Service and deposit columns
            $table->decimal('service_charge', 11, 2)->default(0.00);
            $table->decimal('security_deposit', 11, 2)->default(0.00);

            // Type columns
            $table->string('customer_type')->nullable();
            $table->enum('billing_type', ['prepaid', 'postpaid']);
            $table->enum('connection_type', ['fiber', 'wired', 'wireless']);
            $table->enum('connectivity_type', ['shared', 'dedicated', 'hybrid', 'virtual', 'private', 'other'])->nullable();
            $table->string('client_type')->nullable();
            $table->string('distribution_location')->nullable();

            // Billing options
            $table->boolean('bill_create')->default(true);
            $table->boolean('bill_print')->default(true);
            $table->boolean('bill_email')->default(true);
            $table->boolean('bill_sms')->default(true);
            $table->boolean('bill_fax')->default(false);
            $table->boolean('continue_bill')->default(false);

            // Additional info columns
            $table->string('description')->nullable();
            $table->string('note')->nullable();

            // Foreign key for 'connected_by' (users table)
            $table->unsignedBigInteger('connected_by')->nullable();
            $table->foreign('connected_by')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('official_infos');
    }
};
