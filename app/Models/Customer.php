<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    // Specify the table name if it differs from the plural form of the model name
    protected $table = 'customers';

    // Define the fillable columns (these are the columns you want to allow mass assignment)
    protected $fillable = [
        'full_name',
        'email',
        'phone_number',
        'second_phone_number',
        'address',
        'city',
        'password',
        'status',
    ];

    // If you want to hide certain fields (e.g., password) from being serialized (when returned as JSON), use the $hidden property
    protected $hidden = [
        'password',
    ];

    // If you want to define custom date formats or handle timestamps differently, you can use:
    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
