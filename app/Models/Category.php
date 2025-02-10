<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $primaryKey = 'category_id';
    public $timestamps = false; // No timestamps in schema

    protected $fillable = [
        'category_name',
        'cat_image',
        'status',
    ];

    // Relationship with Brands
    public function brands()
    {
        return $this->hasMany(Brand::class, 'cat_id', 'category_id');
    }
}
