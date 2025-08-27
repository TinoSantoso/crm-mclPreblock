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
        Schema::create('fwd_district_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('region_id')->index();
            $table->string('region_name', 20)->index();
            $table->string('area_name', 25)->index();
            $table->unsignedTinyInteger('area_code')->unique();
            $table->unsignedTinyInteger('region_code')->index();
            $table->unsignedTinyInteger('district_seq')->nullable()->index();
            $table->unsignedBigInteger('user_last_update')->nullable();
            $table->string('updated_by_name', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fwd_district_areas');
    }
};
