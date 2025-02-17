<?php

namespace App\Http\Controllers;

use App\Models\BoardingHouse;
use App\Models\Room;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class BoardingHouseController extends Controller
{
    public function getAvailableBoardingHouses(Request $request)
    {
        try {
            $query = $request->get('query');
            
            $boardingHousesQuery = BoardingHouse::query();
            
            // If search query is provided, filter results
            if ($query) {
                $boardingHousesQuery->where(function($q) use ($query) {
                    $q->where('address', 'LIKE', "%{$query}%")
                      ->orWhere('name', 'LIKE', "%{$query}%");
                });
            }

            $boardingHouses = $boardingHousesQuery
                ->with(['rooms'])
                ->get()
                ->map(function($house) {
                    return [
                        'boarding_house_id' => $house->boarding_house_id,
                        'name' => $house->name,
                        'description' => $house->description,
                        'address' => $house->address,
                        'landlord_name' => $house->landlord_name,
                        'landlord_phone' => $house->landlord_phone,
                        'bh_images' => $house->bh_images,
                        'rooms' => $house->rooms->map(function($room) {
                            return [
                                'room_id' => $room->room_id,
                                'room_name' => $room->room_name,
                                'bed_quantity' => $room->bed_quantity,
                                'room_images' => $room->room_images ? 
                                    (str_starts_with($room->room_images, 'http') ? 
                                        $room->room_images : 
                                        '/storage/' . $room->room_images) 
                                    : null,
                                'status' => $room->status,
                                'map_link' => $room->map_link,
                                'price' => $room->price
                            ];
                        })
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $boardingHouses
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching boarding houses: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch boarding houses'
            ], 500);
        }
    }

    public function getBoardingHouseRooms($id)
    {
        try {
            Log::info('Fetching rooms for boarding house: ' . $id);
            
            $boardingHouse = BoardingHouse::find($id);
            
            if (!$boardingHouse) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Boarding house not found'
                ], 404);
            }
            
            $rooms = Room::where('boarding_house_id', $id)->get();

            $formattedRooms = $rooms->map(function($room) {
                return [
                    'room_id' => $room->room_id,
                    'room_name' => $room->room_name,
                    'bed_quantity' => $room->bed_quantity,
                    'room_images' => $room->room_images,
                    'status' => $room->status,
                    'map_link' => $room->map_link,
                    'price' => $room->price
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $formattedRooms
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching rooms: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch rooms: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBoardingHouseDetails($id)
    {
        try {
            $boardingHouse = BoardingHouse::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => [
                    'boarding_house_id' => $boardingHouse->boarding_house_id,
                    'name' => $boardingHouse->name,
                    'description' => $boardingHouse->description,
                    'address' => $boardingHouse->address,
                    'landlord_name' => $boardingHouse->landlord_name,
                    'landlord_phone' => $boardingHouse->landlord_phone,
                    'bh_images' => $boardingHouse->bh_images,
                    'price' => $boardingHouse->price,
                    'shared_price' => $boardingHouse->shared_price,
                    'map_url' => $boardingHouse->map_url
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch boarding house: ' . $e->getMessage()
            ], 404);
        }
    }

    public function getRoomDetails($id)
    {
        try {
            $room = Room::with('boardingHouse')->findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'room_name' => $room->room_name,
                    'price' => $room->price,
                    'bed_quantity' => $room->bed_quantity,
                    'status' => $room->status,
                    'room_images' => $room->room_images,
                    'description' => $room->description,
                    'map_link' => $room->map_link,
                    'boarding_house_id' => $room->boarding_house_id,
                    'boarding_house' => $room->boardingHouse ? [
                        'boarding_house_id' => $room->boardingHouse->boarding_house_id,
                        'name' => $room->boardingHouse->name,
                        'address' => $room->boardingHouse->address,
                        'landlord_phone' => $room->boardingHouse->landlord_phone,
                        'description' => $room->boardingHouse->description
                    ] : null
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching room details: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch room details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeBooking(Request $request)
    {
        try {
            // Log the incoming request
            \Log::info('Booking request received:', $request->all());

            // Validate request
            $validated = $request->validate([
                'room_id' => 'required|exists:rooms,room_id',
                'check_in_date' => 'required|date',
                'check_out_date' => 'required|date|after:check_in_date',
            ]);

            // Get authenticated user
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Check if room is available
            $room = Room::where('room_id', $validated['room_id'])
                ->where('status', 'available')
                ->first();

            if (!$room) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Room is not available'
                ], 400);
            }

            // Create booking
            $booking = new Booking();
            $booking->user_id = $user->user_id;
            $booking->room_id = $validated['room_id'];
            $booking->check_in_date = $validated['check_in_date'];
            $booking->check_out_date = $validated['check_out_date'];
            $booking->status = 'pending';
            $booking->save();

            // Update room status
            $room->status = 'booked';
            $room->save();

            \Log::info('Booking created successfully:', $booking->toArray());

            return response()->json([
                'status' => 'success',
                'message' => 'Booking created successfully',
                'data' => $booking
            ]);

        } catch (\Exception $e) {
            \Log::error('Booking creation failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $boardingHouse = BoardingHouse::findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'data' => $boardingHouse
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Boarding house not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch boarding house'
            ], 500);
        }
    }

    public function getBoardingHouseReviews($id)
    {
        try {
            $reviews = Review::with(['user:user_id,name'])
                ->where('boarding_house_id', $id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($review) {
                    return [
                        'id' => $review->review_id,
                        'userName' => $review->user->name,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'date' => $review->created_at,
                        'image' => $review->image ? Storage::url($review->image) : null,
                        'room_id' => $review->room_id
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $reviews
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch reviews: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRoomReviews($roomId)
    {
        try {
            $reviews = Review::with(['user:user_id,name,profile_picture'])
                ->where('room_id', $roomId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($review) {
                    return [
                        'id' => $review->review_id,
                        'user_id' => $review->user_id,
                        'userName' => $review->user->name,
                        'user_profile' => $review->user->profile_picture,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'date' => $review->created_at,
                        'images' => $review->images ? json_decode($review->images) : []
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $reviews
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching room reviews: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch room reviews: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeRoomReview(Request $request)
    {
        try {
            $request->validate([
                'room_id' => 'required|exists:rooms,room_id',
                'boarding_house_id' => 'required|exists:boarding_houses,boarding_house_id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string|max:1000',
                'images.*' => 'nullable|image|max:5120' // 5MB max per image
            ]);

            // Initialize empty array for image paths
            $imagePaths = [];
            
            // Handle image uploads if present
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('review-images', 'public');
                    $imagePaths[] = $path;
                }
            }

            // Create the review with the correct attributes
            $review = Review::create([
                'user_id' => Auth::id(),
                'room_id' => $request->room_id,
                'boarding_house_id' => $request->boarding_house_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'images' => !empty($imagePaths) ? json_encode($imagePaths) : null
            ]);

            // Transform image paths to full URLs for response
            $imageUrls = array_map(function($path) {
                return Storage::url($path);
            }, $imagePaths);

            // Return success response with formatted data
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $review->review_id,
                    'userName' => Auth::user()->name,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'date' => $review->created_at,
                    'images' => $imageUrls
                ]
            ]);

        } catch (ValidationException $e) {
            Log::error('Validation error:', $e->errors());
            return response()->json([
                'status' => 'error',
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error storing review:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit review: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateReview(Request $request, $id)
    {
        try {
            $review = Review::findOrFail($id);
            
            // Get existing images from the request
            $existingImages = json_decode($request->existing_images, true) ?? [];
            
            // Handle new uploaded images
            $newImages = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('review-images', 'public');
                    $newImages[] = $path;
                }
            }
            
            // Combine existing and new images
            $allImages = array_merge($existingImages, $newImages);
            
            // Update the review
            $review->rating = $request->rating;
            $review->comment = $request->comment;
            $review->images = json_encode($allImages);
            $review->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Review updated successfully',
                'data' => [
                    'id' => $review->id,
                    'userName' => Auth::user()->name,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'date' => $review->created_at,
                    'images' => $allImages
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update review: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteReview($id)
    {
        try {
            // Find the review
            $review = Review::findOrFail($id);
            
            // Check if the user owns this review
            if ($review->user_id !== Auth::id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to delete this review'
                ], 403);
            }

            // Delete any associated images
            if ($review->images) {
                $images = json_decode($review->images);
                foreach ($images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            // Delete the review
            $review->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Review deleted successfully'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Review not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error deleting review:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete review: ' . $e->getMessage()
            ], 500);
        }
    }
} 
