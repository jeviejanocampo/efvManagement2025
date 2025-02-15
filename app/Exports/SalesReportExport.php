<?php

namespace App\Exports;

use App\Models\OrderDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SalesReportExport implements FromCollection, WithHeadings, WithCustomStartCell, WithEvents
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return OrderDetail::where('product_status', 'Completed')
            ->when($this->startDate, function ($query) {
                $query->whereDate('created_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                $query->whereDate('created_at', '<=', $this->endDate);
            })
            ->select('order_detail_id', 'product_name', 'price', 'quantity', 'total_price') 
            ->get();
    }
    
    public function headings(): array
    {
        return [
            'ID',
            'Product Name',
            'Price',
            'Quantity',
            'Total Price',
            'Date Created',
        ];
    }

    public function startCell(): string
    {
        return 'A3'; // Start data below the custom header
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Add the "EFV Auto Parts Management System" header
                $sheet->mergeCells('A1:F1'); // Merge cells for the header
                $sheet->setCellValue('A1', 'EFV Auto Parts Management System'); // Add system name
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'size' => 16,
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Add the "Sales Report" header below
                $sheet->mergeCells('A2:F2'); // Merge cells for the second header
                $sheet->setCellValue('A2', 'Sales Report'); // Add the report title
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'size' => 14,
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Add the current date below the "Sales Report" title
                $sheet->mergeCells('A3:F3'); // Merge cells for the date
                $sheet->setCellValue('A3', 'Date: ' . now()->format('F j, Y')); // Add current date
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 12,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Add the footer at the bottom-right
                $totalRows = $sheet->getHighestRow() + 1; // Get the next available row after data
                $sheet->setCellValue('F' . ($totalRows + 1), 'Owner'); // Place "Owner" in the bottom-right
                $sheet->getStyle('F' . ($totalRows + 1))->applyFromArray([
                    'font' => [
                        'italic' => true,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                ]);

                // Calculate and display TOTAL SALES
                $dataStartRow = 4; // Adjust to match the starting row of your data
                $dataEndRow = $sheet->getHighestRow(); // Get the last row of the data
                $totalSalesCell = 'E' . ($dataEndRow + 1); // Place TOTAL SALES below the 'Total Price' column

                // Add the label and calculation
                $sheet->setCellValue('D' . ($dataEndRow + 1), 'TOTAL SALES:'); // Add label
                $sheet->setCellValue($totalSalesCell, '=SUM(E' . $dataStartRow . ':E' . $dataEndRow . ')'); // Add formula
                $sheet->getStyle('D' . ($dataEndRow + 1))->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                ]);
                $sheet->getStyle($totalSalesCell)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    ],
                ]);
            },
        ];
    }


}
