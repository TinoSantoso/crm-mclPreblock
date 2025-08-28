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
        Schema::create('fwd_period', function (Blueprint $table) {
            $table->id();
            $table->integer('month')->comment('Month (1-12)');
            $table->integer('year')->comment('Year (e.g., 2025)');
            $table->integer('swd_amount')->unsigned()->comment('Standard Working Days count');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fwd_period');
    }
};
