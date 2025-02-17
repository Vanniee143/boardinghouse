<?php

namespace App\Traits;

use App\Models\Activity;

trait LogsActivity
{
    public function logActivity($type, $description, $action = null, $status = null, $metadata = null)
    {
        try {
            Activity::create([
                'user_id' => auth()->id(),
                'type' => $type,
                'description' => $description,
                'action' => $action,
                'status' => $status,
                'metadata' => $metadata,
                'related_type' => get_class($this),
                'related_id' => $this->id
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }
} 