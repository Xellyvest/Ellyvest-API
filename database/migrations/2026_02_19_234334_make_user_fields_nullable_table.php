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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->uuid('country_id')->nullable()->change();
            $table->uuid('state_id')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('zipcode')->nullable()->change();
            $table->string('nationality')->nullable()->change();
            $table->string('experience')->nullable()->change();
            $table->string('employed')->nullable()->change();
            $table->uuid('currency_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
