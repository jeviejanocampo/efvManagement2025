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
    protected $totalQuantity;
    protected $totalSales;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $records = OrderDetail::where('product_status', 'Completed')
            ->when($this->startDate, fn ($query) => $query->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($query) => $query->whereDate('created_at', '<=', $this->endDate))
            ->select('order_detail_id', 'product_name', 'price', 'quantity', 'total_price')
            ->get();

        $this->totalQuantity = $records->sum('quantity');
        $this->totalSales = $records->sum('total_price');

        return $records;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Product Name',
            'Price',
            'Quantity',
            'Total Price',
        ];
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Headers
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'EFV Auto Parts Management System');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['size' => 16, 'bold' => true],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A2:E2');
                $sheet->setCellValue('A2', 'Sales Report');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 14, 'bold' => true],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A3:E3');
                $sheet->setCellValue('A3', 'Date: ' . now()->format('F j, Y'));
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 12],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                $dataStartRow = 4;
                $dataEndRow = $sheet->getDelegate()->getHighestRow();
                $summaryStartRow = $dataEndRow + 2;

                $sheet->setCellValue('C' . $summaryStartRow, 'TOTAL SALES:');
                $sheet->setCellValue('D' . $summaryStartRow, number_format($this->totalSales, 2));

                $sheet->setCellValue('C' . ($summaryStartRow + 1), 'TOTAL ITEMS:');
                $sheet->setCellValue('D' . ($summaryStartRow + 1), $this->totalQuantity);

                $sheet->setCellValue('C' . ($summaryStartRow + 2), 'SALES AMOUNT:');
                $sheet->setCellValue('D' . ($summaryStartRow + 2), number_format($this->totalSales, 2));

                foreach (range($summaryStartRow, $summaryStartRow + 2) as $row) {
                    $sheet->getStyle('C' . $row)->applyFromArray([
                        'font' => ['bold' => true],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT],
                    ]);
                    $sheet->getStyle('D' . $row)->applyFromArray([
                        'font' => ['bold' => true],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                    ]);
                }

                // Signature
                $sheet->setCellValue('E' . ($summaryStartRow + 4), 'Owner');
                $sheet->getStyle('E' . ($summaryStartRow + 4))->applyFromArray([
                    'font' => ['italic' => true],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT],
                ]);

                // Widen Product Name column
                $sheet->getDelegate()->getColumnDimension('B')->setWidth(35);
            },
        ];
    }
}
