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
        Schema::create('employee_visits', function (Blueprint $table) {
            $table->id();
            $table->string('area');
            $table->string('employee_id');
            $table->string('employee_name');
            $table->year('period_year');
            $table->tinyInteger('period_month');
            $table->integer('standard_working_days')->default(0);
            $table->integer('total_offline_visits')->default(0);
            $table->integer('total_online_visits')->default(0);
            $table->integer('adjustment_from_asm')->default(0);
            $table->text('note_adjustment')->nullable();
            $table->integer('final_total_visits')->default(0);
            $table->timestamps();
            
            $table->unique(['employee_id', 'period_year', 'period_month'], 'employee_period_unique');
            $table->index(['area', 'period_year', 'period_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_visits');
    }
};
