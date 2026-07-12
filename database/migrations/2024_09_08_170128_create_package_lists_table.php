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
        Schema::create('package_lists', function (Blueprint $table) {
            $table->id();
            $table->string('package')->unique();
            $table->decimal('price')->default(0.00);
            $table->string('description')->nullable();
            $table->string('merchant_company')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_lists');
    }
};
