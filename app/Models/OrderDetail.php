<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    // Define the table name (optional, Laravel automatically assumes table name based on model name)
    protected $table = 'order_details';

    // Define the fillable attributes
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
}
