<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_visit_details', function (Blueprint $table) {
            $table->id();
            $table->string('trans_no');
            $table->string('account');
            $table->string('contact');
            $table->string('cat');    // Category field
            $table->integer('vf');     // VF field
            $table->string('class');  // Class field
            $table->date('visit_date');
            $table->tinyInteger('is_visited')->default(0); // Use 0 or 1 instead of boolean
            $table->text('remark')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_visit_details');
    }
};
