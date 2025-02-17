<?php

namespace App\Http\Controllers;

use App\Models\Support;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SupportController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'subject' => 'required|string',
                'description' => 'required|string',
                'priority' => 'required|in:low,medium,high',
                'user_id' => 'required|exists:users,user_id',
                'user_name' => 'required|string',
                'status' => 'required|in:pending,in_progress,resolved'
            ]);

            $support = Support::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Support request created successfully',
                'data' => $support
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating support request: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create support request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAllRequests()
    {
        try {
            $requests = Support::with('user')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($request) {
                    return [
                        'support_id' => $request->support_id,
                        'subject' => $request->subject,
                        'description' => $request->description,
                        'priority' => $request->priority,
                        'status' => $request->status,
                        'user_name' => $request->user_name,
                        'user_profile_picture' => $request->user->profile_picture ? 
                            asset('storage/' . $request->user->profile_picture) : null,
                        'created_at' => $request->created_at,
                        'updated_at' => $request->updated_at
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $requests
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching support requests: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch support requests: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'status' => 'required|in:pending,in_progress,resolved'
            ]);

            $supportRequest = Support::findOrFail($id);
            $supportRequest->status = $validatedData['status'];
            $supportRequest->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Support request status updated successfully',
                'data' => $supportRequest
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating support request status: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update support request status: ' . $e->getMessage()
            ], 500);
        }
    }
} 
