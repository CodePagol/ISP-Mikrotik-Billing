<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Change ssh_port and api_port columns to string to hold encrypted values
        Schema::table('router_lists', function (Blueprint $table) {
            $table->string('ssh_port')->nullable()->change();
            $table->string('api_port')->nullable()->change();
        });

        // 2. Encrypt existing records
        $routers = DB::table('router_lists')->get();
        foreach ($routers as $router) {
            $newData = [];
            foreach (['ip_address', 'username', 'password', 'ssh_port', 'api_port'] as $field) {
                $val = $router->$field;
                if ($val !== null && $val !== '') {
                    try {
                        decrypt($val);
                        // Already encrypted
                    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                        // Plaintext, encrypt it
                        $newData[$field] = encrypt($val);
                    }
                }
            }
            if (!empty($newData)) {
                DB::table('router_lists')->where('id', $router->id)->update($newData);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Decrypt existing records
        $routers = DB::table('router_lists')->get();
        foreach ($routers as $router) {
            $newData = [];
            foreach (['ip_address', 'username', 'password', 'ssh_port', 'api_port'] as $field) {
                $val = $router->$field;
                if ($val !== null && $val !== '') {
                    try {
                        $newData[$field] = decrypt($val);
                    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                        // Already plaintext
                    }
                }
            }
            if (!empty($newData)) {
                DB::table('router_lists')->where('id', $router->id)->update($newData);
            }
        }

        // 2. Change columns back to integer
        Schema::table('router_lists', function (Blueprint $table) {
            $table->integer('ssh_port')->nullable()->change();
            $table->integer('api_port')->nullable()->change();
        });
    }
};
