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
        Schema::create('fwd_dtl', function (Blueprint $table) {
            $table->id();
            $table->string('transNo', 50)->nullable();
            $table->string('empName', 150)->nullable();
            $table->integer('adjustment')->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->foreign('transNo')->references('transNo')->on('fwd_hdr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fwd_dtl');
    }
};
