<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;
use App\Http\Controllers\Controller;

class ReportSalesDistrictController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index()
    {
        return view('backend.report.rpt_salesPs');
    }

    /**
     * Export filtered sales customer reports to Excel.
     * Accepts POST JSON: { period: 'YYYY-MM-DD', districts: [..] }
     */
    public function exportByCustomer()
    {
        $input = request()->json()->all();
        $period = $input['period'] ?? null;
        $districts = $input['districts'] ?? [];

        $baseQuery = \App\Models\SalesCustomerReport::query();  //change to real datasource
        if ($period) {
            $baseQuery->where('period_month', date('m', strtotime($period)))
              ->where('period_year', date('Y', strtotime($period)));
        }
        if (!empty($districts)) {
            $baseQuery->whereIn('distName', $districts);
        }

        $areaNames = (clone $baseQuery)->groupBy('areaName')->pluck('areaName');
        if ($areaNames->isEmpty()) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'No data found for the selected filters.'
            ], 200);
        }

        $periodParam = $period ? date('Y-m', strtotime($period)) : 'all-time';
        $zipFileName = 'Sales By Customer_' . $periodParam . '.zip';
        $zipFilePath = storage_path('app/' . $zipFileName);

        $tempExportPath = storage_path('app/temp_exports/' . uniqid());
        File::makeDirectory($tempExportPath, 0755, true);

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            // Clean up if zip creation fails
            File::deleteDirectory($tempExportPath);
            throw new \Exception("Error: Could not create zip archive.");
        }

        try {
            foreach ($areaNames as $areaName) {
                // Clone the base query again for each area
                $areaQuery = (clone $baseQuery)->where('areaName', $areaName);

                // Get the last date of the period month
                $lastDate = $period ? date('t-m-Y', strtotime($period . '-01')) : 'all-time';
                $areaPart = $areaName ? ' - ' . preg_replace('/[^A-Za-z0-9\-_]/', '_', $areaName) : '';
                $excelFileName = 'Sales By Customer ' . $lastDate . $areaPart . '.xlsx';
                $tempExcelFilePath = $tempExportPath . '/' . $excelFileName;
                
                $storePath = 'temp_exports/' . basename($tempExportPath) . '/' . $excelFileName;

                // Use your existing SalesReportsExport class to create and store the file
                Excel::store(
                    new \App\Exports\SalesReportsExport($areaQuery),
                    $storePath
                );

                $zip->addFile($tempExcelFilePath, $excelFileName);
            }
        } finally {
            $zip->close();
            File::deleteDirectory($tempExportPath);
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }
}
