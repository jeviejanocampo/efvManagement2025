<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    // Define the table name (if it's not the plural form of the model name)
    protected $table = 'activity_logs';

    protected $primaryKey = 'id'; // Update this to match your table's primary key

    // Define the fillable columns (for mass assignment)
    protected $fillable = [
        'user_id',
        'activity',
        'role',
        'created_at',
        'updated_at',
    ];

    // Optionally, define relationships if needed. For example, if ActivityLog belongs to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id', 'id');
    }

    // Disable timestamps if the table doesn't have `created_at` or `updated_at` columns
    // public $timestamps = false;
}
