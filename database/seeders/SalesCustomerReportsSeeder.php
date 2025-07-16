<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalesCustomerReportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $areaNames = [
            "Northern Sumatra", "Bali Nusra", "Eastern Jakarta", "Ecommerce", "Far East", "Kalimantan",
            "Northern East Java", "Northern Central Java", "West Java", "Western Jakarta",
            "Southern East Java", "Southern Central Java", "Southern Sumatra"
        ];
        $distNames = ["East", "Ecommerce", "West"];
        $empNames = [
            '210402', '230501', '191230', '191105', '241101',
            '210403', '230502', '191231', '191106', '241102'
        ];

        $currentYear = Carbon::now()->year;
        for ($i = 1; $i <= 2999; $i++) {
            DB::table('sales_customer_reports')->insert([
            'distName' => $distNames[array_rand($distNames)],
            'areaName' => $areaNames[array_rand($areaNames)],
            'empName' => $empNames[($i - 1) % count($empNames)],
            'oriBranchName' => 'Original Branch ' . rand(1, 5),
            'branchName' => 'Branch ' . rand(1, 10),
            'channelName' => 'Channel ' . rand(1, 3),
            'referenceCode' => 'REF' . str_pad($i, 4, '0', STR_PAD_LEFT),
            'custCode' => 'CUST' . str_pad($i, 4, '0', STR_PAD_LEFT),
            'custName' => 'Customer ' . $i,
            'prodGroup' => 'Group ' . rand(1, 4),
            'prod_name' => 'Product ' . rand(1, 8),
            'gross' => rand(1000, 10000),
            'qty' => rand(10, 100),
            'discount' => rand(100, 1000),
            'netSales' => rand(900, 9000),
            'ly_gross' => rand(1000, 10000),
            'ly_qty' => rand(10, 100),
            'ly_discount' => rand(100, 1000),
            'ly_netSales' => rand(900, 9000),
            'period_year' => $currentYear,
            'period_month' => rand(6, 7),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            ]);
        }
    }
}
