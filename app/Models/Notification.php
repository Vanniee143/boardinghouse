<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'message',
        'link',
        'read',
        'has_attachment',
        'attachment_type'
    ];

    protected $casts = [
        'read' => 'boolean',
        'has_attachment' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
} 