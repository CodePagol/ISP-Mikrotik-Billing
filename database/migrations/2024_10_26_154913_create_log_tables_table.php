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
        Schema::create('log_tables', function (Blueprint $table) {
            $table->id();
            $table->string('table_name'); // Name of the table being logged
            $table->string('action'); // Action type (e.g., update, delete)
            $table->unsignedBigInteger('record_id')->nullable(); // ID of the associated record
            $table->json('old_data')->nullable(); // Old data
            $table->json('new_data')->nullable(); // New data
            $table->unsignedBigInteger('user_id')->nullable(); // ID of the user who made the change
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_tables');
    }
};
