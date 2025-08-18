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
        Schema::create('positions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('asset_id');
            $table->enum('asset_type', ['crypto', 'stocks', 'etf']);
            $table->decimal('price', 18, 2)->default(0);
            $table->decimal('quantity', 18, 8)->default(0);
            $table->decimal('amount', 18, 2)->default(0);
            $table->enum('status', ['open', 'locked']);
            $table->string('entry')->nullable();
            $table->string('exit')->nullable();
            $table->string('leverage')->nullable();
            $table->string('interval')->nullable();
            $table->string('tp')->nullable();
            $table->string('sl')->nullable();
            $table->enum('account', ['wallet', 'brokerage', 'auto', 'savings']);
            $table->decimal('extra', 18, 2)->default(0);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('asset_id')
                ->references('id')
                ->on('assets')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('positions');

        Schema::enableForeignKeyConstraints();
    }
};
