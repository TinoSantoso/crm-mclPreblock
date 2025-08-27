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
        Schema::create('dim_period', function (Blueprint $table) {
            $table->id();
            
            // Primary dimension key
            $table->integer('period_key')->unique()->index();
            
            // Date hierarchy fields
            $table->integer('year_num')->index();
            $table->integer('quarter_num');
            $table->string('quarter_name', 10);
            $table->integer('month_num');
            $table->string('month_name', 15);
            $table->integer('week_num');
            
            // Date fields
            $table->date('full_date')->index();
            $table->integer('day_of_week');
            $table->string('day_name', 15);
            
            // Additional fields
            $table->integer('audit_id')->default(0);
            $table->date('date_name');
            $table->integer('working_day');
            $table->integer('working_day_left');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dim_period');
    }
};
