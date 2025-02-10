<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $table = 'brands';
    protected $primaryKey = 'brand_id';
    public $timestamps = true; // Laravel will handle created_at & updated_at automatically

    protected $fillable = [
        'cat_id',
        'brand_name',
        'brand_image',
        'status',
    ];

    // Relationship with Category
    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id', 'category_id');
    }
}
