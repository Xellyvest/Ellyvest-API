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
        Schema::table('auto_plan_investments', function (Blueprint $table) {
            $table->boolean('credited')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auto_plan_investments', function (Blueprint $table) {
            $table->dropColumn([
                'credited'
            ]);
        });
    }
};
