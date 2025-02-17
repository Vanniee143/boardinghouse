<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
} 