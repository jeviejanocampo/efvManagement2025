<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundOrder extends Model
{
    use HasFactory;

    protected $table = 'refund_order'; // Define the table name

    protected $primaryKey = 'refund_id'; // Define the primary key

    public $timestamps = true; // Enables created_at and updated_at timestamps

    protected $fillable = [
        'order_id',
        'user_id',
        'refund_reason',
        'original_total',
        'final_total',
        'change_given',
        'processed_by',
        'refund_method',
        'status',
        'overall_status',
        'refund_completed_at',
        'extra_details',
        'details_selected'
    ];
    
    // Relationship with Order (Assuming Order Model Exists)
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // Relationship with User (Assuming User Model Exists)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // In Refund.php
    public function processedByUser()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }



    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id', 'id');
    }

        // RefundOrder Model
    public function orderReference()
    {
        return $this->hasOne(OrderReference::class, 'order_id', 'order_id');
    }

}
