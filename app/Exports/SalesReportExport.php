<?php

namespace App\Exports;

use App\Models\OrderDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesReportExport implements FromCollection, WithHeadings
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
            ->select('order_detail_id', 'product_name', 'price', 'quantity', 'total_price', 'created_at')
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
}
