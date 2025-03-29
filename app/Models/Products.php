<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;

    protected $table = 'products'; // Define the table name

    protected $primaryKey = 'product_id'; // Define primary key

    public $timestamps = true; // Enable created_at and updated_at

    protected $fillable = [
        'model_id',
        'brand_id',
        'model_name',
        'brand_name',
        'price',
        'description',
        'm_part_id',
        'model_img',
        'stocks_quantity',
        'status'
    ];

    // Relationship with Brand (assuming you have a Brand model)
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function model()
    {
        return $this->belongsTo(Models::class, 'model_id');
    }

}
