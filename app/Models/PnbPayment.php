<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PnbPayment extends Model
{
    use HasFactory;

    // The name of the table
    protected $table = 'pnb_payment';

    // The primary key for the model
    protected $primaryKey = 'id';

    // Disable the timestamps if the created_at and updated_at are not used (set to true to use them)
    public $timestamps = true;

    // Define the fields that are mass assignable
    protected $fillable = [
        'order_id',
        'image',
        'status',
    ];

    // Optionally, you can define the date format if needed
    protected $dateFormat = 'Y-m-d H:i:s';

    // If you want to define any relationships, you can do so here, for example:
    // public function order()
    // {
    //     return $this->belongsTo(Order::class, 'order_id');
    // }
}
