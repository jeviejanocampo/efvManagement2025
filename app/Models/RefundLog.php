<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundLog extends Model
{
    use HasFactory;

    protected $table = 'refund_log';

    protected $fillable = [
        'user_id',
        'activity',
        'role',
        'refunded_at',
    ];

    public $timestamps = true;

    const CREATED_AT = 'refunded_at';
    const UPDATED_AT = 'updated_at';
}
