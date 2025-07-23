<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WorkingDayController extends Controller
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

    /**
     * Display the actual working day view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('backend.workingday.actual_working_day');
    }

    /**
     * Get actual working day data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        // Get parameters from request
        $year = $request->input('year');
        $month = $request->input('month');
        
        $data = [
            [
                'id' => 1,
                'employee_id' => 'EMP001',
                'employee_name' => 'John Doe',
                'year' => $year ?: date('Y'),
                'month' => $month ?: date('m'),
                'working_days' => 22,
                'actual_days' => 20,
                'holidays' => 2,
                'absences' => 0,
                'status' => 'Active'
            ],
            [
                'id' => 2,
                'employee_id' => 'EMP002',
                'employee_name' => 'Jane Smith',
                'year' => $year ?: date('Y'),
                'month' => $month ?: date('m'),
                'working_days' => 22,
                'actual_days' => 18,
                'holidays' => 2,
                'absences' => 2,
                'status' => 'Active'
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Data retrieved successfully'
        ]);
    }
}