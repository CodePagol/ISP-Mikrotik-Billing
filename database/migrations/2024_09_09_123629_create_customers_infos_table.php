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
        Schema::create('customers_infos', function (Blueprint $table) {
            $table->id();
            $table->string('customer_unique_id')->unique();
            $table->string('customer_name')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('parents_name')->nullable();
            $table->string('spouse_name')->nullable();
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('alternative_mobile')->nullable();
            $table->string('identification_no')->nullable();
            $table->string('profession')->nullable();
            $table->string('photo_url')->nullable();
            $table->integer('disable_count')->default(0)->nullable();
            // $table->string('router_name')->nullable();
            // $table->foreign('router_name')->references('router_name')->on('router_lists')->cascadeOnUpdate()->nullOnDelete();
            // $table->string('ppp_user')->nullable();
            // $table->foreign('ppp_user')->references('username')->on('p_p_p_secrets')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('ppp_user_id')->nullable()->constrained('p_p_p_secrets')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('connection_date')->nullable();
            $table->string('package_name')->nullable();
            $table->foreign('package_name')->references('package')->on('package_lists')->cascadeOnUpdate()->nullOnDelete();
            // $table->string('status')->default('pending');
            $table->enum('status', ['pending', 'active', 'inactive', 'deleted', 'disable', 'free'])->default('pending');
            $table->softDeletes();
            // $table->timestamp('deleted_at')->nullable();
            // $table->boolean('auto_disable')->default(true);
            // $table->timestamp('auto_disable_date')->nullable();
            // $table->tinyInteger('auto_disable_month')->nullable();
            // $table->timestamp('extra_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_infos');
    }
};
