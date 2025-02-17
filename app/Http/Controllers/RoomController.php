<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function getRoomReviews($id)
    {
        try {
            $reviews = Review::with(['user:user_id,name'])
                ->where('room_id', $id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($review) {
                    return [
                        'id' => $review->review_id,
                        'userName' => $review->user->name,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'date' => $review->created_at,
                        'room_id' => $review->room_id,
                        'image' => $review->image ? Storage::url($review->image) : null
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $reviews
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch room reviews: ' . $e->getMessage()
            ], 500);
        }
    }
} 