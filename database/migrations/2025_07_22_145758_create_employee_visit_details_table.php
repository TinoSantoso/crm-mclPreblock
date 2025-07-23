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
        Schema::create('employee_visit_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_visit_id')->constrained('employee_visits')->onDelete('cascade');
            $table->tinyInteger('visit_day');
            $table->enum('visit_type', ['offline', 'online'])->default('offline');
            $table->string('client_name')->nullable();
            $table->text('visit_notes')->nullable();
            $table->timestamp('visit_datetime')->nullable();
            $table->timestamps();
            
            $table->index(['employee_visit_id', 'visit_day']);
            $table->index('visit_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_visit_details');
    }
};
