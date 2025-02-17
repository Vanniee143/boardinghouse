<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Support extends Model
{
    protected $table = 'support_requests';
    protected $primaryKey = 'support_id';
    
    protected $fillable = [
        'subject',
        'description',
        'priority',
        'user_id',
        'user_name',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
} 