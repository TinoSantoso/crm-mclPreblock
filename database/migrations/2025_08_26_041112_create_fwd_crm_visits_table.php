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
        Schema::create('fwd_crm_visits', function (Blueprint $table) {
            $table->id();
            
            // Basic visit information
            $table->enum('status', ['Scheduled', 'Completed', 'Cancelled', 'In Progress'])->default('Scheduled');
            $table->string('subject', 100)->index();
            
            // Account information
            $table->bigInteger('account_number')->unsigned()->nullable()->index();
            $table->string('account_name', 100);
            
            // Visit details
            $table->string('visit_type', 50)->default('Account Visit');
            $table->string('visit_subtype', 50)->nullable();
            
            // Time scheduling
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->integer('duration')->comment('Duration in minutes');
            
            // Visit characteristics
            $table->boolean('is_phone_call')->default(false);
            $table->text('objectives')->nullable();
            
            // Employee and hierarchy
            $table->string('employee_name', 100)->index();
            $table->string('sales_hierarchy', 100)->nullable();
            
            // Contact information
            $table->string('attendees', 200)->nullable();
            $table->bigInteger('contact_id')->unsigned()->nullable();
            
            // Visit execution tracking
            $table->boolean('is_remote_mode')->nullable();
            $table->boolean('is_checked_in')->default(false);
            $table->boolean('is_checked_out')->default(false);
            $table->timestamp('created_on')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fwd_crm_visits');
    }
};
