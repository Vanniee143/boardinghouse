<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function getNotifications($userId)
    {
        try {
            Log::info('Fetching notifications for user: ' . $userId);
            
            $notifications = Notification::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
            
            Log::info('Found notifications:', [
                'count' => $notifications->count(),
                'notifications' => $notifications->toArray()
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $notifications
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching notifications: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch notifications'
            ], 500);
        }
    }

    public function markAsRead($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->read = true;
            $notification->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    public function markAllAsRead($userId)
    {
        try {
            Notification::where('user_id', $userId)
                ->where('read', false)
                ->update(['read' => true]);

            return response()->json([
                'status' => 'success',
                'message' => 'All notifications marked as read'
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark all notifications as read'
            ], 500);
        }
    }

    public function deleteNotification($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification deleted'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting notification: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete notification'
            ], 500);
        }
    }

    public function createNotification(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,user_id',
                'type' => 'required|string',
                'message' => 'required|string',
                'link' => 'nullable|string'
            ]);

            $notification = Notification::create([
                'user_id' => $validatedData['user_id'],
                'type' => $validatedData['type'],
                'message' => $validatedData['message'],
                'link' => $validatedData['link'] ?? null,
                'read' => false
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Notification created successfully',
                'data' => $notification
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating notification: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create notification'
            ], 500);
        }
    }
} 