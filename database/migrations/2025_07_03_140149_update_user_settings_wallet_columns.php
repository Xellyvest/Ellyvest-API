<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('user_settings', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['connect_wallet_network', 'connect_wallet_phrase']);
            
            // Add new columns
            $table->timestamp('connected_wallet_at')->nullable();
            $table->json('connected_wallet')->nullable();
            $table->boolean('is_connect_activated')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->string('connect_wallet_network')->nullable();
            $table->string('connect_wallet_phrase')->nullable();
            $table->dropColumn(['connected_wallet_at', 'connected_wallet']);
        });
    }
};
