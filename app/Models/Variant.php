<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    use HasFactory;

    protected $table = 'variants'; // Explicitly set the table name

    protected $primaryKey = 'variant_id'; // Set the primary key

    public $timestamps = true; // Enables created_at & updated_at

    protected $fillable = [
        'model_id',
        'product_name',
        'variant_image',
        'part_id',
        'price',
        'specification',
        'status',
        'description',
        'stocks_quantity',
    ];

    // Relationship with the Models table
    public function model()
    {
        return $this->belongsTo(Models::class, 'model_id', 'model_id');
    }
}
