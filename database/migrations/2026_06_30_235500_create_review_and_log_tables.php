<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create user_login_logs table
        Schema::create('user_login_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('authenticatable_id');
            $table->string('authenticatable_type');
            $table->string('username');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('action'); // 'login' or 'logout'
            $table->timestamps();

            $table->index(['authenticatable_id', 'authenticatable_type']);
        });

        // 2. Create customer_reviews table
        Schema::create('customer_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ppp_user_id')->constrained('p_p_p_secrets')->onDelete('cascade');
            $table->integer('rating');
            $table->text('comment');
            $table->integer('edit_count')->default(0);
            $table->boolean('show_on_site')->default(false);
            $table->timestamps();
        });

        // 3. Add login_count to p_p_p_secrets table
        Schema::table('p_p_p_secrets', function (Blueprint $table) {
            $table->integer('login_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_reviews');
        Schema::dropIfExists('user_login_logs');
        Schema::table('p_p_p_secrets', function (Blueprint $table) {
            $table->dropColumn('login_count');
        });
    }
};
