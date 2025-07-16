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
        Schema::create('crm_details', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key

            // Deduced from CSV headers
            $table->smallInteger('year')->nullable();
            $table->tinyInteger('month')->nullable();
            $table->string('employee_name')->nullable();
            $table->text('contact_name')->nullable(); // Changed from string to text for longer values
            $table->string('account')->nullable(); // Contains numbers, commas, and text
            $table->string('contact_categorization', 10)->nullable(); // Short codes like 'D', 'A'
            $table->integer('target_call')->default(0)->nullable();
            $table->integer('target_coverage')->default(0)->nullable();

            $table->integer('specialty_id')->nullable(); // CSV shows 0 when specialty text is NULL
            $table->string('specialty')->nullable();
            $table->integer('area_id')->nullable(); // CSV shows 0 when Area text is NULL
            $table->string('area')->nullable();
            $table->integer('department_id')->nullable(); // CSV shows 0 when Department text is NULL
            $table->string('department')->nullable();
            $table->integer('role_id')->nullable(); // CSV shows 0 when role text is NULL
            $table->string('role')->nullable();
            $table->integer('level_id')->nullable(); // CSV shows 0 when level_contact text is NULL
            $table->string('level_contact')->nullable();
            $table->integer('unit_id')->nullable(); // CSV shows 0 when unit text is NULL
            $table->string('unit')->nullable();
            $table->integer('category_id')->nullable(); // CSV shows 0 when category text is NULL
            $table->string('category')->nullable();

            $table->boolean('out_of_town')->nullable(); // CSV has NULL or 1
            $table->string('ps_name')->nullable();
            $table->string('branch_short_name', 20)->nullable(); // e.g., SBY
            $table->string('refer_code')->nullable(); // Could be alphanumeric
            $table->string('account_number')->nullable(); // Could be alphanumeric
            $table->string('specialty_hcp')->nullable();
            $table->text('notes')->nullable(); // Can be longer text, CSV has 'NULL' or actual notes
            $table->string('emp_id')->nullable(); // Employee ID, indexed for faster lookups

            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_details');
    }
};
