<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderReference extends Model
{
    use HasFactory;

    protected $table = 'order_reference'; // Explicitly define table name

    protected $fillable = [
        'order_id',
        'reference_id',
    ];

    public $timestamps = true; // Enables created_at & updated_at

    // Relationship with Order model
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // Relationship with Reference model (assuming it exists)
    public function reference()
    {
        return $this->belongsTo(Reference::class, 'reference_id');
    }

    public function orderReference()
    {
        return $this->hasOne(OrderReference::class, 'order_id', 'order_id');
    }

    // In OrderReference.php model
    public function refundOrder()
    {
        return $this->belongsTo(RefundOrder::class, 'order_id', 'order_id');
    }


}
