<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Models extends Model
{
    use HasFactory;

    // If your table is named 'models' and it doesn't follow the default naming convention
    protected $table = 'models';

    // Define the fillable columns (optional if you plan to insert data)
    protected $fillable = ['model_id', 'model_img'];

    // Disable timestamps if your table does not have created_at and updated_at columns
    public $timestamps = false;
}
