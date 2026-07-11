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
        Schema::create('permitted_urls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')->nullable()->constrained('router_lists')->onDelete('cascade');
            $table->string('url_or_ip');
            $table->enum('type', ['url', 'ip'])->default('url');
            $table->string('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permitted_urls');
    }
};
