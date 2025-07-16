<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\CrmVisit;
use App\Models\CrmVisitDetail;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;



class PreblockMclController extends Controller
{
    protected $jakartaTz;

    public function __construct()
    {
        $this->jakartaTz = new \DateTimeZone('Asia/Jakarta');
    }

    /**
     * Retrieve all crm_details records (for internal use)
     */
    public function getAllCrmDetails(Request $request)
    {
        $query = DB::table('crm_details')
            ->where('emp_id', $request->user()->employee_id)
            ->where('target_call', '>', 0);

        // If period is provided as a query param, filter by period (expects 'YYYY-MM')
        $period = $request->query('period');
        Log::info('getAllCrmDetails called with period: ' . $period);
        if ($period) {
            try {
                $dt = new \DateTime($period . '-01');
                $year = $dt->format('Y');
                $month = $dt->format('m');
                // $query->where('year', $year)->where('month', $month);
            } catch (\Exception $e) {
                // Ignore invalid period, return all
            }
        }

        return $query->get();
    }

    /**
     * Return crm_details data for dxDataGrid.
     */
    public function index(Request $request)
    {
        return view('backend.preblock.preblock_mcl');
    }
    
    /**
     * Show visit form view
     */
    public function showVisit()
    {
        return view('backend.preblock.preblock_visit_mcl');
    }

    /**
     * Store CRM visit and details from JSON data
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $header = $data['header'] ?? [];
        $details = $data['details'] ?? [];

        // Check for duplicate (emp_id + account) before storing
        $empId = $request->user()->employee_id ?? null;
        if ($empId && is_array($details)) {
            foreach ($details as $row) {
                $account = $row['institusi'] ?? null;
                if ($account && $this->checkVisitDetailByEmpAndAccount($empId, $account)) {
                    return response()->json([
                    'status' => 'failed',
                    'error' => "Duplicate entry: Employee ID $empId already has account $account in visit details."
                    ], 409);
                }
            }
        }

        // Transform period to year and month
        $period = $header['period'] ?? null;
        $year = null;
        $month = null;
        if ($period) {
            try {
                $dt = new \DateTime($period);
                $year = $dt->format('Y');
                $month = $dt->format('m');
            } catch (\Exception $e) {
                $year = null;
                $month = null;
            }
        }

        $maxAttempts = 5;
        $attempt = 0;
        do {
            DB::beginTransaction();
            try {
                // Generate trans_no inside transaction with DBlocking
                $yearShort = date('y');
                $prefix = 'TR' . $yearShort . '-';
                $last = DB::table('crm_visits')
                    ->where('trans_no', 'like', $prefix . '%')
                    ->orderByDesc('trans_no')
                    ->lockForUpdate()
                    ->first();
                $nextNumber = 1;
                if ($last && preg_match('/^' . preg_quote($prefix, '/') . '(\d{5})$/', $last->trans_no, $matches)) {
                    $nextNumber = intval($matches[1]) + 1;
                }
                $nextTransNo = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
                
                // Insert into crm_visits
                DB::table('crm_visits')->insert([
                    'trans_no' => $nextTransNo,
                    'emp_id' => $empId ?? null,
                    'year' => $year,
                    'month' => $month,
                    'remark' => $header['remark'] ?? null,
                    'created_at' => Carbon::now(),
                ]);

                // Prepare details for bulk insert
                $detailRows = [];
                foreach ($details as $row) {
                    $visitDate = null;
                    if (!empty($row['period'])) {
                        try {
                            $dt = new \DateTime($row['period'], new \DateTimeZone('UTC'));
                            $dt->setTimezone($this->jakartaTz);
                            $visitDate = $dt->format('Y-m-d');
                        } catch (\Exception $e) {
                            $visitDate = null;
                        }
                    }
                    $detailRows[] = [
                        'trans_no' => $nextTransNo,
                        'account' => $row['institusi'] ?? null,
                        'contact' => $row['individu'] ?? null,
                        'visit_date' => $visitDate,
                        'cat' => $row['cat'] ?? null,
                        'vf' => (int)$row['vf'] ?? null,
                        'class' => $row['class'] ?? null,
                        'remark' => $row['remark'] ?? null,
                        'created_at' => Carbon::now(),
                    ];
                }
                foreach (array_chunk($detailRows, 100) as $chunk) {
                    DB::table('crm_visit_details')->insert($chunk);
                }

                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'header' => array_merge($header, ['trans_no' => $nextTransNo]),
                    'details' => $details
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();
                // Duplicate entry error code for MySQL: 1062
                if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
                    $attempt++;
                    if ($attempt >= $maxAttempts) {
                        return response()->json([
                            'status' => 'failed',
                            'error' => 'Could not generate unique trans_no after multiple attempts.'
                        ], 500);
                    }
                    usleep(100000); // Wait 100ms before retry
                    continue;
                }
                return response()->json([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'header' => $header,
                    'details' => $details
                ], 500);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'header' => $header,
                    'details' => $details
                ], 500);
            }
        } while ($attempt < $maxAttempts);
    }

    /**
     * Update CRM visit and details from JSON data
     */
    public function update(Request $request)
    {
        $data = $request->all();
        $header = $data['header'] ?? [];
        $details = $data['details'] ?? [];
        
        // Transform period to year and month
        $period = $header['period'] ?? null;
        $year = null;
        $month = null;
        if ($period) {
            try {
                $dt = new \DateTime($period);
                $year = $dt->format('Y');
                $month = $dt->format('m');
            } catch (\Exception $e) {
                $year = null;
                $month = null;
            }
        }
        
        DB::beginTransaction();
        try {
            // Update crm_visits
            DB::table('crm_visits')
                ->where('trans_no', $header['trans_no'])
                ->update([
                    'year' => $year,
                    'month' => $month,
                    'remark' => $header['remark'] ?? null,
                    'updated_at' => Carbon::now(),
                ]);

            // Delete all details for this trans_no
            DB::table('crm_visit_details')->where('trans_no', $header['trans_no'])->delete();

            // Insert new details using Eloquent for better reliability
            foreach ($details as $row) {
                $visitDate = null;
                if (!empty($row['period'])) {
                    try {
                        $dt = new \DateTime($row['period'], new \DateTimeZone('UTC'));
                        $dt->setTimezone($this->jakartaTz);
                        $visitDate = $dt->format('Y-m-d');
                    } catch (\Exception $e) {
                        $visitDate = null;
                    }
                }
                if (!isset($row['institusi'], $row['individu'], $row['cat'], $row['vf'], $row['class']) || $visitDate === null) {
                    Log::warning('Skipped crm_visit_detail row due to missing required fields', ['row' => $row]);
                    continue;
                }
                try {
                    $detail = new \App\Models\CrmVisitDetail();
                    $detail->trans_no = $header['trans_no'];
                    $detail->account = $row['institusi'];
                    $detail->contact = $row['individu'];
                    $detail->visit_date = $visitDate;
                    $detail->cat = $row['cat'];
                    $detail->vf = (int)$row['vf'];
                    $detail->class = $row['class'];
                    $detail->remark = $row['remark'] ?? null;
                    $detail->created_at = Carbon::now();
                    $detail->updated_at = Carbon::now();
                    $detail->save();
                } catch (\Exception $e) {
                    Log::error('Failed to insert crm_visit_detail row', ['row' => $row, 'error' => $e->getMessage()]);
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'header' => $header,
                'details' => $details
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'header' => $header,
                'details' => $details
            ], 500);
        }
    }

    /**
     * Get all crm_visits for Tab List grid using Eloquent, including details, filtered by emp_id, year, month, and visit_date.
     */
    public function getVisits(Request $request)
    {
        $query = \App\Models\CrmVisit::with(['details' => function($query) use ($request) {
            if ($visitDate = $request->query('visit_date')) {
                $query->whereDate('visit_date', $visitDate);
            }
        }])->orderByDesc('created_at');

        $empId = $request->user()->employee_id;
        $year = $request->query('year');
        $month = $request->query('month');

        if (!empty($empId)) {
            $query->where('emp_id', $empId);
        }
        if (!empty($year)) {
            $query->where('year', $year);
        }
        if (!empty($month)) {
            $query->where('month', $month);
        }

        // Only get visits that have matching details after filtering
        if ($request->query('visit_date')) {
            $query->has('details');
        }

        $visits = $query->get();

        try {
            // If all visits have empty details, return empty array with 200 OK
            if ($visits->isEmpty() || $visits->every(function($visit) {
                return $visit->details->isEmpty();
            })) {
                return response()->json([
                    'data' => [],
                    'message' => 'No visits found.'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error processing visits in getVisits: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage()
            ], 500);
        }

        // Filter out visits with no details after relationship constraints
        $visits = $visits->filter(function($visit) {
            return $visit->details->isNotEmpty();
        });

        return response()->json($visits);
    }


    /**
     * Remove the specified CRM visit and its details by trans_no (Laravel standard: destroy)
     */
    public function destroy($id)
    {
        $transNo = $id;
        if (!$transNo) {
            return response()->json([
                'status' => 'failed',
                'error' => 'trans_no is required.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Delete details first
            DB::table('crm_visit_details')->where('trans_no', $transNo)->delete();
            // Delete header
            DB::table('crm_visits')->where('trans_no', $transNo)->delete();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'trans_no' => $transNo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'trans_no' => $transNo
            ], 500);
        }
    }

    /**
     * Generate the next unique transaction number for crm_visits (format: TRyy-00001)
     */
    public function generateTransNo()
    {
        $year = date('y');
        $prefix = 'TR' . $year . '-';
        // Find the highest trans_no for this year
        $last = DB::table('crm_visits')
            ->where('trans_no', 'like', $prefix . '%')
            ->orderByDesc('trans_no')
            ->value('trans_no');
        $nextNumber = 1;
        if ($last && preg_match('/^' . preg_quote($prefix, '/') . '(\d{5})$/', $last, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        }
        $nextTransNo = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        return response()->json(['trans_no' => $nextTransNo]);
    }

    /**
     * Check if a crm_visit_details record exists for a given emp_id and account
     * Returns true if exists, false otherwise
     */
    protected function checkVisitDetailByEmpAndAccount($empId, $account)
    {
        $exists = DB::table('crm_visit_details')
            ->join('crm_visits', 'crm_visits.trans_no', '=', 'crm_visit_details.trans_no')
            ->where('crm_visits.emp_id', $empId)
            ->where('crm_visit_details.account', $account)
            ->exists();
        return $exists;
    }

    /**
     * Update is_visited status for a crm_visit_details row by id
     */
    public function updateIsVisited(Request $request, $id)
    {
        $isVisited = $request->input('is_visited');
        if (!in_array($isVisited, [0, 1, '0', '1'], true)) {
            return response()->json([
                'status' => 'failed',
                'error' => 'Invalid is_visited value.'
            ], 400);
        }
        $affected = DB::table('crm_visit_details')
            ->where('id', $id)
            ->update(['is_visited' => $isVisited, 'updated_at' => Carbon::now()]);
        if ($affected) {
            return response()->json(['status' => 'success']);
        } else {
            return response()->json([
                'status' => 'failed',
                'error' => 'No record updated.'
            ], 404);
        }
    }

    /**
     * Export CRM visits as PDF using dompdf
     */
    public function exportPdf(Request $request)
    {
        $empId = $request->user()->employee_id;
        $year = $request->input('year'); 
        $month = $request->input('month');

        // configuration from environment variables with defaults
        $fontSizeTable = env('PDF_FONT_SIZE_TABLE');
        $fontSizeTitle = env('PDF_FONT_SIZE_TITLE');
        $fontSizeSubtitle = env('PDF_FONT_SIZE_SUBTITLE');
        $colWidthNo = env('PDF_COL_WIDTH_NO');
        $colWidthInstitusi = env('PDF_COL_WIDTH_INSTITUSI');
        $colWidthSpecialty = env('PDF_COL_WIDTH_SPECIALTY');
        $colWidthIndividu = env('PDF_COL_WIDTH_INDIVIDU');
        $visitDateTopMargin = env('PDF_VISIT_DATE_TOP_MARGIN');
        $tableTopMargin = env('PDF_TABLE_TOP_MARGIN');

        // Fetch the data
        $query = \App\Models\CrmVisit::with('details')->orderByDesc('created_at');
        if (!empty($empId)) {
            $query->where('emp_id', $empId);
        }
        if (!empty($year)) {
            $query->where('year', $year);
        }
        if (!empty($month)) {
            $query->where('month', $month);
        }
        $visits = $query->get();

        // Group by visit_date
        $grouped = [];
        foreach ($visits as $visit) {
            if (is_iterable($visit->details)) {
                foreach ($visit->details as $detail) {
                    $visitDate = $detail->visit_date ?? 'No Date';
                    $grouped[$visitDate][] = $detail;
                }
            }
        }

        $visitDates = array_keys($grouped);
        sort($visitDates);
        $count = count($visitDates);

        $html = '<html><head>';
        $html .= '<style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        @page {
            margin: 10mm 20mm 10mm 20mm;
        }
        .container {
            width: 98%;
            margin: 0 auto;
        }
        table {
            border-collapse: collapse;
            border-spacing: 0;
            width: 100%;
            border: 0.3px solid #ddd;
            font-size: ' . $fontSizeTable . 'px;
            margin-bottom: 4px;
            page-break-inside: auto;
        }
        table:not(:first-of-type) {
            margin-top: 20mm;
        }
        thead {
            display: table-header-group;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        th, td {
            text-align: left;
            padding: 1px 2px;
            word-break: break-word;
            border: 0.3px solid #ddd;
            line-height: 1.1;
        }
        th {
            background-color: #333;
            color: white;
        }
        .visit-date-title {
            font-weight: bold;
            margin-bottom: 1px;
            font-size: ' . $fontSizeSubtitle . 'px;
            margin-top: ' . $visitDateTopMargin . 'px;
            page-break-before: auto;
            page-break-after: avoid;
        }
        .period-label {
            margin-left: 8px;
            display: inline-block;
            font-size: ' . $fontSizeSubtitle . 'px;
        }
        h2 {
            margin: 0;
            margin-top: 5px;
            margin-right: 16px;
            font-size: ' . $fontSizeTitle . 'px;
            display: inline-block;
        }
        </style>';
        $html .= '</head><body><div class="container">';
        $html .= '<div style="display: flex; align-items: center;">'
            . '<h2>MCL Preblock</h2>'
            . '<div class="period-label"><b>Period:</b> ' . htmlspecialchars($year) . '-' . htmlspecialchars($month) . '</div>'
            . '</div>';

        if ($count === 0) {
            $html .= '<div style="color:#888;font-size:' . $fontSizeSubtitle . 'px;">No details data found for this selection.</div>';
        } else {
            foreach ($visitDates as $visitDate) {
            $details = $grouped[$visitDate];
            $html .= '<div class="visit-date-title">Visit Date: ' . htmlspecialchars($visitDate) . '</div>';
            $html .= '<table>';
            $html .= '<thead><tr>'
                . '<th style="width:' . $colWidthNo . '%;">No</th>'
                . '<th style="width:' . $colWidthInstitusi . '%; text-align: center;">Account</th>'
                . '<th style="width:' . $colWidthSpecialty . '%; text-align: center;">Specialty</th>'
                . '<th style="width:' . $colWidthIndividu . '%; text-align: center;">Contact</th>'
                . '<th style="width:10%; text-align: center;">Visit Date</th>'
                . '</tr></thead><tbody>';
            $rowNo = 1;
            foreach ($details as $detail) {
                $html .= '<tr>'
                . '<td>' . $rowNo++ . '</td>'
                . '<td>' . htmlspecialchars($detail->account ?? '') . '</td>'
                . '<td style="text-align: center;">' . htmlspecialchars($detail->class ?? '') . '</td>'
                . '<td>' . htmlspecialchars($detail->contact ?? '') . '</td>'
                . '<td style="text-align: center;">' . htmlspecialchars($visitDate) . '</td>'
                . '</tr>';
            }
            $html .= '</tbody></table>';
            }
        }
        $html .= '</div></body></html>';

        // Generate PDF using dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Stream the PDF to the browser
        $now = (new \DateTime('now', $this->jakartaTz))->format('Ymd_His');
        $filename = "crm-preblock-report_{$now}.pdf";
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}