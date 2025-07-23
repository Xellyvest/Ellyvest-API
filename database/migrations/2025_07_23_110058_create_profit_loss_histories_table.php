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
        Schema::create('profit_loss_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->decimal('wallet_value', 15, 2)->default(0);
            $table->decimal('brokerage_value', 15, 2)->default(0);
            $table->decimal('auto_value', 15, 2)->default(0);
            $table->decimal('savings_value', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profit_loss_histories');
    }
};
