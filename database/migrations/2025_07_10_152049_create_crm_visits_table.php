<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_visits', function (Blueprint $table) {
            $table->id();
            $table->string('trans_no')->unique();
            $table->string('emp_id');
            $table->text('remark')->nullable();
            $table->smallInteger('year'); // Period year
            $table->tinyInteger('month'); // Period month
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_visits');
    }
};
