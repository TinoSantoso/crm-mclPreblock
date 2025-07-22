<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Excel;

class SalesReportsExport implements FromQuery, WithHeadings, WithMapping, Responsable, WithStyles, WithColumnFormatting
{
    use \Maatwebsite\Excel\Concerns\Exportable;

    private $writerType = Excel::XLSX;
    protected $query;

    /**
     * @param Builder $query
     */
    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * Apply styles to the worksheet (bold first row)
     */
    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        // Bold first row (row 1)
        $sheet->getStyle('A1:S2')->getFont()->setBold(true);
        // Center and merge currentMonth (L1:O1) and lastYearMonth (P1:S1)
        $sheet->mergeCells('L1:O1');
        $sheet->mergeCells('P1:S1');
        $sheet->getStyle('L1:O1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('P1:S1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Apply background color to both heading rows (A1:S2) to cover LY Nett
        $sheet->getStyle('A1:S2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE6EEF5');
    }    

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        // First row: merged headings
        $headings[] = [
            'District',
            'Area Name',
            'Employee Name',
            'Original Branch',
            'Branch',
            'Channel Name',
            'Reference Code',
            'Customer Code',
            'Customer Name',
            'Product Group',
            'Product Name',
            'Current Month', '', '', '',
            'Last Year', '', '', '',
        ];

        // Second row: sub-headings
        $headings[] = [
            '', '', '', '', '', '', '', '', '', '', '',
            'Gross', 'Qty', 'Discount', 'Nett',
            'LY Gross', 'LY Qty', 'LY Discount', 'LY Nett',
        ];

        return $headings;
    }

    public function map($row): array
    {
        return [
            $row->distName ?? '',
            $row->areaName ?? '',
            $row->empName ?? '',
            $row->oriBranchName ?? '',
            $row->branchName ?? '',
            $row->channelName ?? '',
            $row->referenceCode ?? '',
            $row->custCode ?? '',
            $row->custName ?? '',
            $row->prodGroup ?? '',
            $row->prod_name ?? '',
            number_format($row->gross, 0, '.', ','),
            number_format($row->qty, 0, '.', ','),
            number_format($row->discount, 0, '.', ','),
            number_format($row->netSales, 0, '.', ','),
            number_format($row->ly_gross, 0, '.', ','),
            number_format($row->ly_qty, 0, '.', ','),
            number_format($row->ly_discount, 0, '.', ','),
            number_format($row->ly_netSales, 0, '.', ','),
        ];
    }

    /**
     * Set column formats for numeric columns using PhpSpreadsheet's NumberFormat
     */
    public function columnFormats(): array
    {
        return [
            'L' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,
            'M' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,
            'N' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,
            'O' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,
            'P' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,
            'Q' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,
            'R' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,
            'S' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,
        ];
    }
}
