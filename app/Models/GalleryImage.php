<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryImage extends Model
{
    protected $table = 'gallery_images';

    protected $fillable = [
        'model_id',
        'variant_id',
        'image_url'
    ];

    public $timestamps = false;
}
