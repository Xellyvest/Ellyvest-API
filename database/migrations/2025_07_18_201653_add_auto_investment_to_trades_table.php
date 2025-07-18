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
        Schema::table('trades', function (Blueprint $table) {
            $table->uuid('auto_plan_investment_id')->nullable()->after('account');
            
            $table->foreign('auto_plan_investment_id')
                ->references('id')
                ->on('auto_plan_investments')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            // Reverse the changes if needed
            $table->dropColumn([
                'auto_plan_investment_id'
            ]);
        });
    }
};
