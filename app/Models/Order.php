<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Define the table name explicitly (if it's not the plural of the model name)
    protected $table = 'orders';

    protected $primaryKey = 'order_id'; // Update this to match your table's primary key


    // Specify the fillable fields (to prevent mass assignment vulnerabilities)
    protected $fillable = [
        'order_id',
        'user_id',
        'name',
        'reference_id',
        'total_items',
        'total_price',
        'original_total_amount',
        'cash_received',
        'customers_change',
        'order_notes',
        'pickup_date',
        'pickup_location',
        'payment_method',
        'scan_status',  
        'status',
        'overall_status',
        'created_at',
        'updated_at'
    ];

    // If your table has timestamps (created_at, updated_at), ensure the model knows
    public $timestamps = true;

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id', 'id');
    }
}
