<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Products; // Ensure this is imported at the top

class Models extends Model
{
    use HasFactory;

    // Define the table name
    protected $table = 'models';

    // Define the primary key
    protected $primaryKey = 'model_id';

    // Allow mass assignment for these fields
    protected $fillable = [
        'model_name',
        'model_img',
        'price',
        'brand_id',
        'w_variant',
        'status',
    ];

    // Enable timestamps if your table has created_at and updated_at columns
    public $timestamps = true;

    // Relationship: Each model belongs to a brand
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    public function products()
    {
        return $this->hasMany(Products::class, 'model_id', 'model_id');
    }
    

    public function getTotalStockQuantityAttribute()
    {
        return $this->products()->sum('stocks_quantity');
    }



}
