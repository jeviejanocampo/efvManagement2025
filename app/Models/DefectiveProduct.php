<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefectiveProduct extends Model
{
    use HasFactory;

    protected $table = 'defective_product';

    protected $fillable = [
        'order_detail_id',
        'order_id',
        'model_id',
        'variant_id',
        'product_name',
        'brand_name',
        'quantity',
        'price',
        'total_price',
        'product_status',
        'created_at',
        'updated_at',
        'part_id',
        'm_part_id',
    ];

    public $timestamps = false;

    public function orderReference()
    {
        return $this->belongsTo(OrderReference::class, 'order_id', 'order_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
}
