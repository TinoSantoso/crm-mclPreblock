<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_customer_reports', function (Blueprint $table) {
            $table->id();
            $table->string('distName')->nullable(); // District
            $table->string('areaName')->nullable(); // Area Name
            $table->string('empName')->nullable(); // Employee Name
            $table->string('oriBranchName')->nullable(); // Original Branch
            $table->string('branchName')->nullable(); // Branch
            $table->string('channelName')->nullable(); // Channel Name
            $table->string('referenceCode')->nullable(); // Reference Code
            $table->string('custCode')->nullable(); // Customer Code
            $table->string('custName')->nullable(); // Customer Name
            $table->string('prodGroup')->nullable(); // Product Group
            $table->string('prod_name')->nullable(); // Product Name
            $table->integer('period_year')->nullable(); // Year
            $table->integer('period_month')->nullable(); // Month
            // CURRENT MONTH
            $table->decimal('gross', 15, 2)->nullable(); // Gross
            $table->decimal('qty', 15, 2)->nullable(); // Qty
            $table->decimal('discount', 15, 2)->nullable(); // Discount
            $table->decimal('netSales', 15, 2)->nullable(); // Nett
            // LAST YEAR
            $table->decimal('ly_gross', 15, 2)->nullable(); // LY Gross
            $table->decimal('ly_qty', 15, 2)->nullable(); // LY Qty
            $table->decimal('ly_discount', 15, 2)->nullable(); // LY Discount
            $table->decimal('ly_netSales', 15, 2)->nullable(); // LY Nett
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_panel_reports');
    }
};
