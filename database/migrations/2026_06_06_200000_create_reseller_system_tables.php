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
        // 1. resellers table
        Schema::create('resellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('company')->nullable();
            $table->decimal('commission_percentage', 8, 2)->default(10.00);
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        // 2. reseller_commissions table
        Schema::create('reseller_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')->constrained('resellers')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers_infos')->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('package_lists')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->decimal('commission_percentage', 8, 2);
            $table->timestamp('created_at')->useCurrent();
        });

        // 3. reseller_wallet_transactions table
        Schema::create('reseller_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')->constrained('resellers')->cascadeOnDelete();
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();
        });

        // 4. vouchers table
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->decimal('value', 15, 2);
            $table->enum('type', ['fixed_amount', 'package_based']);
            $table->foreignId('package_id')->nullable()->constrained('package_lists')->nullOnDelete();
            $table->enum('status', ['unused', 'used'])->default('unused');
            $table->date('expiry_date');
            $table->foreignId('reseller_id')->constrained('resellers')->cascadeOnDelete();
            $table->foreignId('used_by_customer_id')->nullable()->constrained('customers_infos')->nullOnDelete();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        // 5. reseller_packages mapping table (pivot)
        Schema::create('reseller_packages', function (Blueprint $table) {
            $table->foreignId('reseller_id')->constrained('resellers')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('package_lists')->cascadeOnDelete();
            $table->primary(['reseller_id', 'package_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reseller_packages');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('reseller_wallet_transactions');
        Schema::dropIfExists('reseller_commissions');
        Schema::dropIfExists('resellers');
    }
};
