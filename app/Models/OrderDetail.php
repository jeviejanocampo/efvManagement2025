<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $table = 'order_details';

    protected $primaryKey = 'order_detail_id';

    protected $fillable = [
        'order_id',
        'model_id',
        'variant_id',
        'product_name',
        'brand_name',
        'quantity',
        'price',
        'total_price',
        'product_status',
    ];

    // Define relationship to Models
    public function model()
    {
        return $this->belongsTo(Models::class, 'model_id', 'model_id');
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'm_part_id', 'm_part_id');
    }
    
    public function variant()
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }

}
