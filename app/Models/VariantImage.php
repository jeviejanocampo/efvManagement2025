<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantImage extends Model
{
    protected $table = 'variant_images';

    protected $fillable = [
        'variant_id',
        'image'
    ];
}
