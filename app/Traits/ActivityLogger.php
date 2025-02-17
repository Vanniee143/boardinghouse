<?php

namespace App\Traits;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait ActivityLogger
{
    protected function logActivity($type, $description, $userId = null, $boardingHouseId = null)
    {
        try {
            // Get the authenticated user or use provided userId
            $performedBy = $userId ?? Auth::id() ?? request()->header('X-User-Id');

            if (!$performedBy) {
                Log::warning('No performer ID available for activity log', [
                    'type' => $type,
                    'description' => $description
                ]);
                return;
            }

            Activity::create([
                'type' => $type,
                'description' => $description,
                'performed_by' => $performedBy,
                'boarding_house_id' => $boardingHouseId,
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Error logging activity: ' . $e->getMessage(), [
                'type' => $type,
                'description' => $description,
                'performed_by' => $performedBy ?? null,
                'boarding_house_id' => $boardingHouseId
            ]);
        }
    }
} 