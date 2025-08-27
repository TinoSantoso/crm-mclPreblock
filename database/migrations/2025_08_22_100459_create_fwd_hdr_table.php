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
        Schema::create('fwd_hdr', function (Blueprint $table) {
            $table->id();
            $table->string('transNo', 50)->unique();
            $table->dateTime('transDate')->nullable();
            $table->date('period')->nullable();
            $table->string('area', 50)->nullable();
            $table->string('remark', 255)->nullable();
            $table->boolean('is_posted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fwd_hdr');
    }
};
