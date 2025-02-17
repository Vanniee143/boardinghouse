<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function getRecentActivities()
    {
        try {
            Log::info('Fetching recent activities');
            
            $activities = Activity::with(['performer', 'boardingHouse'])
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($activity) {
                    $formattedDate = Carbon::parse($activity->created_at)->diffForHumans();
                    
                    return [
                        'activity_id' => $activity->activity_id,
                        'type' => $activity->type,
                        'description' => $activity->description,
                        'created_at' => $formattedDate,
                        'timestamp' => $activity->created_at->toDateTimeString(),
                        'boarding_house' => $activity->boardingHouse ? [
                            'id' => $activity->boardingHouse->boarding_house_id,
                            'name' => $activity->boardingHouse->name
                        ] : null,
                        'performer' => $activity->performer ? [
                            'name' => $activity->performer->name,
                            'profile_picture' => $activity->formatted_profile_picture,
                            'user_type' => $activity->performer->userType
                        ] : [
                            'name' => 'System',
                            'profile_picture' => null,
                            'user_type' => 'system'
                        ]
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $activities
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getRecentActivities: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch activities'
            ], 500);
        }
    }

    public function getLandlordRecentActivities(Request $request)
    {
        try {
            Log::info('Fetching recent activities for landlord');
            
            $landlordId = $request->user()->user_id;
            
            $activities = Activity::with(['performer', 'boardingHouse'])
                ->where(function($query) use ($landlordId) {
                    $query->where('performer_id', $landlordId)
                          ->orWhereHas('boardingHouse', function($q) use ($landlordId) {
                              $q->where('landlord_id', $landlordId);
                          });
                })
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($activity) {
                    $formattedDate = Carbon::parse($activity->created_at)->diffForHumans();
                    
                    return [
                        'activity_id' => $activity->activity_id,
                        'type' => $activity->type,
                        'description' => $activity->description,
                        'created_at' => $formattedDate,
                        'timestamp' => $activity->created_at->toDateTimeString(),
                        'boarding_house' => $activity->boardingHouse ? [
                            'id' => $activity->boardingHouse->boarding_house_id,
                            'name' => $activity->boardingHouse->name
                        ] : null,
                        'performer' => $activity->performer ? [
                            'name' => $activity->performer->name,
                            'profile_picture' => $activity->formatted_profile_picture,
                            'user_type' => $activity->performer->userType
                        ] : [
                            'name' => 'System',
                            'profile_picture' => null,
                            'user_type' => 'system'
                        ]
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $activities
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getLandlordRecentActivities: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch activities'
            ], 500);
        }
    }
} 