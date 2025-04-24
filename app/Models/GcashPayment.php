<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GcashPayment extends Model
{
    use HasFactory;

    // Specify the table name if it's not the plural form of the model name
    protected $table = 'gcash_payment'; 

    // Define the fillable fields (columns you can insert data into)
    protected $fillable = [
        'order_id',
        'image',
        'status',
    ];

    // Define the default values for status if needed
    protected $attributes = [
        'status' => 'Cancelled', // Default value for status
    ];

    // Timestamps
    public $timestamps = true;

    // Optionally define the date format
    protected $dateFormat = 'Y-m-d H:i:s';
}
