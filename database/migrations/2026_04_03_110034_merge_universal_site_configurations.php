<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create or Rebuild the Universal MainSiteData table
        Schema::dropIfExists('main_site_data');
        Schema::create('main_site_data', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100)->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        // 2. Migrate existing data from Legacy SiteSettings if table exists
        if (Schema::hasTable('site_settings')) {
            $settings = DB::table('site_settings')->first();

            if ($settings) {
                $dataToMigrate = (array) $settings;
                // Remove ID and Timestamps from keys to save
                unset($dataToMigrate['id'], $dataToMigrate['created_at'], $dataToMigrate['updated_at']);

                foreach ($dataToMigrate as $key => $value) {
                    if ($value !== null) {
                        DB::table('main_site_data')->updateOrInsert(
                            ['type' => $key],
                            ['value' => $value, 'created_at' => now(), 'updated_at' => now()]
                        );
                    }
                }
            }

            // 3. Drop the legacy table
            Schema::dropIfExists('site_settings');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-creating site_settings on rollback is complex, usually we just drop the new state
        Schema::dropIfExists('main_site_data');
    }
};
