<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\BoardingHouse;
use App\Models\User;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Review;
use App\Models\PaymentQrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Traits\ActivityLogger;
use Illuminate\Support\Facades\DB;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Notification;

class LandlordController extends Controller
{
    use ActivityLogger;

    public function getRooms()
    {
        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }

            $rooms = Room::with(['boardingHouse', 'bookings'])
                ->whereHas('boardingHouse', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $rooms
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching rooms: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch rooms: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDashboardData()
    {
        try {
            $userId = request()->header('X-User-Id');
            if (!$userId) {
                throw new \Exception('User not authenticated');
            }

            // Verify user is a landlord
            $user = User::find($userId);
            if (!$user || $user->userType !== 'landlord') {
                throw new \Exception('Unauthorized access');
            }

            Log::info('Fetching dashboard data for landlord:', ['user_id' => $userId]);

            // Get boarding houses owned by this landlord
            $boardingHouses = BoardingHouse::where('user_id', $userId)->get();
            $boardingHouseIds = $boardingHouses->pluck('boarding_house_id');

            // Get rooms from these boarding houses
            $rooms = Room::whereIn('boarding_house_id', $boardingHouseIds)->get();

            // Get bookings for these rooms
            $bookings = Booking::whereIn('room_id', $rooms->pluck('room_id'))
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();

            // Get total revenue (all time) from completed payments
            $totalRevenue = Payment::whereIn('booking_id', function($query) use ($rooms) {
                $query->select('booking_id')
                    ->from('bookings')
                    ->whereIn('room_id', $rooms->pluck('room_id'));
            })
            ->where('status', 'completed')
            ->sum('amount');

            // Add debug logging
            Log::info('Revenue calculation debug:', [
                'room_ids' => $rooms->pluck('room_id'),
                'total_revenue' => $totalRevenue,
                'payment_count' => Payment::where('status', 'completed')->count()
            ]);

            // Add logging to check revenue value
            Log::info('Total revenue calculated:', [
                'amount' => $totalRevenue,
            ]);

            // Get payment methods
            $paymentMethods = PaymentQrCode::whereHas('boardingHouse', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->pluck('payment_type')
            ->toArray();

            // Get total reviews
            $totalReviews = Review::whereIn('boarding_house_id', $boardingHouseIds)->count();

            // Get recent bookings with relationships
            $recentBookings = Booking::whereIn('room_id', $rooms->pluck('room_id'))
                ->with(['room:room_id,room_name', 'user:user_id,name'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $dashboardData = [
                'boarding_houses' => [
                    'total' => $boardingHouses->count()
                ],
                'rooms' => [
                    'total' => $rooms->count(),
                    'available' => $rooms->where('status', 'available')->count(),
                    'occupied' => $rooms->where('status', 'occupied')->count()
                ],
                'bookings' => [
                    'total' => array_sum($bookings),
                    'pending' => $bookings['pending'] ?? 0,
                    'confirmed' => $bookings['confirmed'] ?? 0,
                    'cancelled' => $bookings['cancelled'] ?? 0
                ],
                'revenue' => [
                    'amount' => floatval($totalRevenue), // Convert to float
                    'total' => floatval($totalRevenue),  // Convert to float
                    'formatted' => number_format($totalRevenue, 2),
                    'month' => now()->format('F'),
                    'year' => now()->year
                ],
                'accounts' => [
                    'total' => Booking::whereIn('room_id', $rooms->pluck('room_id'))
                        ->distinct('user_id')
                        ->count('user_id')
                ],
                'reviews' => [
                    'total' => $totalReviews
                ],
                'payment_methods' => [
                    'gcash' => in_array('gcash', $paymentMethods),
                    'paymaya' => in_array('paymaya', $paymentMethods)
                ],
                'recent_bookings' => $recentBookings
            ];

            // Add logging for final dashboard data
            Log::info('Dashboard revenue data:', [
                'revenue_data' => $dashboardData['revenue']
            ]);

            Log::info('Dashboard data prepared successfully', ['user_id' => $userId]);

            return response()->json([
                'status' => 'success',
                'data' => $dashboardData
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching landlord dashboard data: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchProfile($id)
    {
        try {
            $user = User::findOrFail($id);
            
            if ($user->userType !== 'landlord') {
                throw new \Exception('Unauthorized access');
            }

            $profilePicture = null;
            if ($user->profile_picture) {
                if (Storage::disk('public')->exists($user->profile_picture)) {
                    $profilePicture = asset('storage/' . $user->profile_picture);
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_picture' => $profilePicture,
                    'phone_number' => $user->phone_number,
                    'gender' => $user->gender,
                    'birthdate' => $user->birthdate
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching landlord profile: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch profile: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            
            if ($user->userType !== 'landlord') {
                throw new \Exception('Unauthorized access');
            }

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id . ',user_id',
                'phone_number' => 'nullable|string|max:20',
                'gender' => 'nullable|string|in:male,female,other',
                'birthdate' => 'nullable|date',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture if it exists
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
                
                // Store new profile picture
                $profilePicturePath = $request->file('profile_picture')->store('profile-pictures', 'public');
                $validatedData['profile_picture'] = $profilePicturePath;
            }

            $user->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating landlord profile: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBoardingHouses()
    {
        try {
            $userId = request()->header('X-User-Id') ?? session('user_id');
            Log::info('Fetching boarding houses for landlord:', ['landlord_id' => $userId]);
            
            // Get boarding houses with relationships
            $boardingHouses = BoardingHouse::with(['rooms', 'creator', 'updater'])
                ->where('user_id', $userId)
                ->get()
                ->map(function($house) {
                    return [
                        'id' => $house->boarding_house_id,
                        'name' => $house->name,
                        'description' => $house->description,
                        'address' => $house->address,
                        'landlord_name' => $house->landlord_name,
                        'landlord_phone' => $house->landlord_phone,
                        'bh_images' => $house->bh_images,
                        'rooms' => $house->rooms->count(),
                        'available' => $house->rooms->where('status', 'available')->count(),
                        'occupied' => $house->rooms->where('status', 'occupied')->count(),
                        'created_at' => $house->created_at,
                        'updated_at' => $house->updated_at,
                        'created_by_name' => $house->creator ? $house->creator->name : 'System',
                        'created_by_type' => $house->creator ? $house->creator->userType : null,
                        'updated_by_name' => $house->updater ? $house->updater->name : 'System',
                        'updated_by_type' => $house->updater ? $house->updater->userType : null
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
                'message' => 'Failed to fetch boarding houses: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRoom($id)
    {
        try {
            $room = Room::with(['boardingHouse', 'creator', 'updater'])->findOrFail($id);
            
            // Get user ID from header or auth
            $userId = request()->header('X-User-Id');
            if (!$userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User ID not provided'
                ], 400);
            }

            // Verify ownership
            if (!$room->boardingHouse || $room->boardingHouse->user_id != $userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to access this room'
                ], 403);
            }

            // Transform room image URL if exists
            $roomImageUrl = null;
            if ($room->room_images) {
                $imagePath = str_replace('public/', '', $room->room_images);
                $roomImageUrl = Storage::disk('public')->exists($imagePath) 
                    ? asset('storage/' . $imagePath)
                    : null;
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'room_id' => $room->room_id,
                    'room_name' => $room->room_name,
                    'bed_quantity' => $room->bed_quantity,
                    'status' => $room->status,
                    'room_images' => $roomImageUrl,
                    'map_link' => $room->map_link,
                    'price' => $room->price,
                    'boarding_house_id' => $room->boarding_house_id,
                    'boarding_house_name' => $room->boardingHouse->name ?? 'Unknown',
                    'created_at' => $room->created_at,
                    'updated_at' => $room->updated_at,
                    'created_by_name' => $room->creator ? $room->creator->name : 'System',
                    'created_by_type' => $room->creator ? $room->creator->userType : null,
                    'updated_by_name' => $room->updater ? $room->updater->name : 'System',
                    'updated_by_type' => $room->updater ? $room->updater->userType : null
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching room: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch room details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateRoom(Request $request, $id)
    {
        try {
            // Find the room
            $room = Room::with('boardingHouse')->findOrFail($id);
            
            // Get the user ID from the request
            $userId = $request->input('updated_by');
            
            // Verify ownership through boarding house
            if (!$room->boardingHouse || $room->boardingHouse->user_id != $userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to update this room'
                ], 403);
            }

            // Validate request data
            $validatedData = $request->validate([
                'room_name' => 'required|string|max:255',
                'bed_quantity' => 'required|integer|min:1',
                'status' => 'required|in:available,occupied,maintenance',
                'room_images' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'map_link' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'boarding_house_id' => 'required|exists:boarding_houses,boarding_house_id',
                'updated_by' => 'required|exists:users,user_id'
            ]);

            // Handle image upload if new image is provided
            if ($request->hasFile('room_images')) {
                // Delete old image if it exists
                if ($room->room_images) {
                    Storage::disk('public')->delete($room->room_images);
                }
                
                // Store new image
                $imagePath = $request->file('room_images')->store('room-images', 'public');
                $validatedData['room_images'] = $imagePath;
            }

            // Update the room
            $room->update($validatedData);

            // Log activity with the landlord's ID
            $this->logActivity(
                'room_updated',
                "Updated room {$room->room_name}",
                $request->input('updated_by'),
                $room->boarding_house_id
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Room updated successfully',
                'data' => $room
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating room: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update room: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addBoardingHouse(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'address' => 'required|string',
                'landlord_name' => 'required|string|max:255',
                'landlord_phone' => 'required|string|max:20',
                'bh_images' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'user_id' => 'required|exists:users,user_id',
                'created_by' => 'required|exists:users,user_id'
            ]);
            
            // Handle image upload
            if ($request->hasFile('bh_images')) {
                $imagePath = $request->file('bh_images')->store('boarding-houses', 'public');
                $validatedData['bh_images'] = $imagePath;
            }

            // Add updated_by field with the same value as created_by
            $validatedData['updated_by'] = $request->input('created_by');

            $boardingHouse = BoardingHouse::create($validatedData);

            // Log activity with the landlord's ID
            $this->logActivity(
                'boarding_house_added',
                "Added new boarding house: {$boardingHouse->name}",
                $request->user_id,  // The landlord's ID
                $boardingHouse->boarding_house_id
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Boarding house added successfully',
                'data' => $boardingHouse
            ]);

        } catch (\Exception $e) {
            \Log::error('Error adding boarding house: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add boarding house: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addRoom(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'boarding_house_id' => 'required|exists:boarding_houses,boarding_house_id',
                'room_name' => 'required|string|max:255',
                'bed_quantity' => 'required|integer|min:1',
                'status' => 'required|in:available,occupied,maintenance',
                'room_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'map_link' => 'nullable|string',
                'price' => 'required|numeric|min:0'
            ]);

            // Get user ID from header
            $userId = request()->header('X-User-Id');
            if (!$userId) {
                throw new \Exception('User not authenticated');
            }

            // Verify boarding house ownership
            $boardingHouse = BoardingHouse::findOrFail($validatedData['boarding_house_id']);
            if ($boardingHouse->user_id != $userId) {
                throw new \Exception('Unauthorized to add room to this boarding house');
            }

            // Handle room image upload
            if ($request->hasFile('room_image')) {
                $imagePath = $request->file('room_image')->store('room-images', 'public');
                $validatedData['room_images'] = $imagePath;
            }

            // Add required fields
            $validatedData['user_id'] = $userId;
            $validatedData['created_by'] = $userId;
            $validatedData['updated_by'] = $userId;

            // Create the room
            $room = Room::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Room added successfully',
                'data' => $room
            ]);

        } catch (\Exception $e) {
            Log::error('Error adding room: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add room: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateBoardingHouse(Request $request, $id)
    {
        try {
            \Log::info('Updating boarding house', ['id' => $id, 'request' => $request->all()]);
            
            $boardingHouse = BoardingHouse::findOrFail($id);
            
            // Check if the boarding house belongs to the logged-in landlord
            if ($boardingHouse->user_id != session('user_id')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Validate the request
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'address' => 'required|string',
                'landlord_name' => 'required|string|max:255',
                'landlord_phone' => 'required|string|max:20',
                'bh_images' => 'nullable|image|max:2048' // 2MB max
            ]);

            // Handle image upload if new image is provided
            if ($request->hasFile('bh_images')) {
                // Delete old image if exists
                if ($boardingHouse->bh_images) {
                    Storage::disk('public')->delete($boardingHouse->bh_images);
                }
                
                // Store new image
                $path = $request->file('bh_images')->store('boarding-houses', 'public');
                $boardingHouse->bh_images = $path;
            }

            // Update other fields
            $boardingHouse->name = $validatedData['name'];
            $boardingHouse->description = $validatedData['description'];
            $boardingHouse->address = $validatedData['address'];
            $boardingHouse->landlord_name = $validatedData['landlord_name'];
            $boardingHouse->landlord_phone = $validatedData['landlord_phone'];
            
            $boardingHouse->save();

            \Log::info('Boarding house updated successfully', ['id' => $id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Boarding house updated successfully',
                'data' => $boardingHouse
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to update boarding house', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update boarding house: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addBooking(Request $request)
    {
        try {
            // ... booking validation and creation code ...
            
            // Add activity for new booking
            $this->addActivity('booking_added', "New booking created for room: {$request->room_name}");
            
            return response()->json([
                'status' => 'success',
                'message' => 'Booking added successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error adding booking: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateBooking(Request $request, $id)
    {
        try {
            // ... booking update code ...
            
            // Add activity for booking update
            $this->addActivity('booking_updated', "Updated booking for room: {$request->room_name}");
            
            return response()->json([
                'status' => 'success',
                'message' => 'Booking updated successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating booking: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addRevenue(Request $request)
    {
        try {
            // ... revenue addition logic ...

            // Add activity for new revenue
            $this->addActivity('revenue_added', "New revenue recorded: ₱{$request->amount}");

            return response()->json([
                'status' => 'success',
                'message' => 'Revenue added successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error adding revenue: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add revenue: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addReview(Request $request)
    {
        try {
            // ... review addition logic ...

            // Add activity for new review
            $this->addActivity('review_received', "New review received for room: {$request->room_name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Review added successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error adding review: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add review: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBoardingHouse($id)
    {
        try {
            $boardingHouse = BoardingHouse::findOrFail($id);
            
            // Check if the boarding house belongs to the logged-in landlord
            if ($boardingHouse->user_id != session('user_id')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Return with full data structure
            return response()->json([
                'status' => 'success',
                'data' => [
                    'name' => $boardingHouse->name,
                    'description' => $boardingHouse->description,
                    'address' => $boardingHouse->address,
                    'landlord_name' => $boardingHouse->landlord_name,
                    'landlord_phone' => $boardingHouse->landlord_phone,
                    'bh_images' => $boardingHouse->bh_images,
                    'user_id' => $boardingHouse->user_id
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch boarding house: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProfile($id)
    {
        try {
            $user = User::findOrFail($id);
            
            if ($user->userType !== 'landlord') {
                throw new \Exception('Unauthorized access');
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_picture' => $user->profile_picture,
                    'phone_number' => $user->phone_number,
                    'gender' => $user->gender,
                    'birthdate' => $user->birthdate
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch profile: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout()
    {
        try {
            session()->flush();
            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to logout: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBookings(Request $request)
    {
        try {
            $userId = $request->header('X-User-Id');
            
            // Get all boarding houses owned by this landlord
            $boardingHouseIds = BoardingHouse::where('user_id', $userId)->pluck('boarding_house_id');
            
            // Get all rooms from these boarding houses
            $roomIds = Room::whereIn('boarding_house_id', $boardingHouseIds)->pluck('room_id');

            // Get bookings with relationships
            $bookings = Booking::whereIn('room_id', $roomIds)
                ->with([
                    'user:user_id,name,email,phone_number,gender,profile_picture',
                    'room' => function($query) {
                        $query->select('room_id', 'room_name', 'price', 'boarding_house_id')
                            ->with(['boardingHouse:boarding_house_id,name']);
                    },
                    'payments' => function($query) {
                        $query->select('payment_id', 'booking_id', 'amount', 'status', 'created_at');
                    }
                ])
                ->get()
                ->map(function($booking) {
                    return [
                        'booking_id' => $booking->booking_id,
                        'status' => $booking->status,
                        'check_in_date' => $booking->check_in_date,
                        'check_out_date' => $booking->check_out_date,
                        'room_id' => $booking->room_id,
                        'room_name' => $booking->room->room_name,
                        'room' => [
                            'id' => $booking->room->room_id,
                            'name' => $booking->room->room_name,
                            'price' => (float)$booking->room->price,
                            'status' => $booking->room->status,
                            'boarding_house' => $booking->room->boardingHouse->name
                        ],
                        'house_name' => $booking->room->boardingHouse->name,
                        'tenant_name' => $booking->user->name,
                        'tenant_profile' => $booking->user->profile_picture ? 
                            asset('storage/' . $booking->user->profile_picture) : null,
                        'user_id' => $booking->user->user_id,
                        'payments' => $booking->payments->map(function($payment) {
                            return [
                                'payment_id' => $payment->payment_id,
                                'amount' => (float)$payment->amount,
                                'status' => $payment->status,
                                'created_at' => $payment->created_at
                            ];
                        })
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $bookings
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching bookings: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch bookings: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getReviews()
    {
        try {
            $userId = request()->header('X-User-Id') ?? session('user_id');
            Log::info('Fetching reviews for landlord:', ['landlord_id' => $userId]);

            $boardingHouseIds = BoardingHouse::where('user_id', $userId)
                ->pluck('boarding_house_id');

            $reviews = Review::select(
                'reviews.*',
                'users.name',
                'users.profile_picture',
                'rooms.room_name',
                'boarding_houses.name as house_name'
            )
            ->join('users', 'reviews.user_id', '=', 'users.user_id')
            ->join('rooms', 'reviews.room_id', '=', 'rooms.room_id')
            ->join('boarding_houses', 'reviews.boarding_house_id', '=', 'boarding_houses.boarding_house_id')
            ->whereIn('reviews.boarding_house_id', $boardingHouseIds)
            ->orderBy('reviews.created_at', 'desc')
            ->get()
            ->map(function ($review) {
                // Format the profile picture URL
                $profilePicture = $review->profile_picture ? 
                    asset('storage/' . $review->profile_picture) : null;

                // Format review images from JSON
                $reviewImages = [];
                if ($review->images) {
                    $images = json_decode($review->images, true);
                    if (is_array($images)) {
                        $reviewImages = array_map(function($image) {
                            return asset('storage/' . $image);
                        }, $images);
                    }
                }

                return [
                    'id' => $review->review_id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at,
                    'name' => $review->name,
                    'profile_picture' => $profilePicture,
                    'room_name' => $review->room_name,
                    'house_name' => $review->house_name,
                    'boarding_house_id' => $review->boarding_house_id,
                    'images' => $reviewImages
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $reviews
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching reviews: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch reviews: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateStayDuration($createdAt)
    {
        $reviewDate = new \DateTime($createdAt);
        $now = new \DateTime();
        $diff = $reviewDate->diff($now);
        
        if ($diff->y > 0) {
            return $diff->y . ' year' . ($diff->y > 1 ? 's' : '');
        } elseif ($diff->m > 0) {
            return $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
        } else {
            return 'Less than a month';
        }
    }

    public function getRevenue($userId)
    {
        try {
            // Verify user exists and is a landlord
            $user = User::findOrFail($userId);
            if ($user->userType !== 'landlord') {
                throw new \Exception('Unauthorized access');
            }

            // Get boarding houses owned by this landlord
            $boardingHouseIds = BoardingHouse::where('user_id', $userId)
                ->pluck('boarding_house_id');

            // Get rooms from these boarding houses
            $roomIds = Room::whereIn('boarding_house_id', $boardingHouseIds)
                ->pluck('room_id');

            // Get all completed bookings and their payments
            $transactions = Booking::whereIn('room_id', $roomIds)
                ->with(['room.boardingHouse', 'user', 'payments'])
                ->get()
                ->map(function($booking) {
                    $payment = $booking->payments->first();
                    
                    // Calculate duration between check-in and check-out dates
                    $checkIn = new \DateTime($booking->check_in_date);
                    $checkOut = new \DateTime($booking->check_out_date);
                    $duration = $checkIn->diff($checkOut)->days;

                    // Log the dates and duration for debugging
                    \Log::info('Booking duration calculation:', [
                        'booking_id' => $booking->booking_id,
                        'check_in' => $booking->check_in_date,
                        'check_out' => $booking->check_out_date,
                        'duration' => $duration,
                        'raw_diff' => $checkIn->diff($checkOut)
                    ]);

                    return [
                        'id' => $booking->booking_id,
                        'date' => $booking->created_at,
                        'house_name' => $booking->room->boardingHouse->name ?? 'Unknown',
                        'room_name' => $booking->room->room_name ?? 'Unknown',
                        'guest_name' => $booking->user->name ?? 'Unknown',
                        'duration' => $duration,
                        'amount' => $payment ? $payment->amount : 0,
                        'status' => $payment ? $payment->status : 'pending',
                        'check_in_date' => $booking->check_in_date,
                        'check_out_date' => $booking->check_out_date
                    ];
                });

            $totalRevenue = $transactions
                ->where('status', 'completed')
                ->sum('amount');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'transactions' => $transactions,
                    'bookingStats' => [
                        'total' => $transactions->count(),
                        'confirmed' => $transactions->where('status', 'completed')->count(),
                        'pending' => $transactions->where('status', 'pending')->count(),
                        'cancelled' => 0
                    ],
                    'revenue' => [
                        'total' => $totalRevenue,
                        'pending' => $transactions->where('status', 'pending')->sum('amount')
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching revenue: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch revenue: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPaymentQRCodes()
    {
        try {
            // Get user ID from header or session
            $userId = request()->header('X-User-Id') ?? session('user_id');
            
            // Get QR codes for the landlord's boarding houses
            $qrCodes = PaymentQrCode::with(['boardingHouse', 'creator'])
                ->whereHas('boardingHouse', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->get()
                ->map(function($qr) {
                    return [
                        'qr_id' => $qr->qr_id,
                        'payment_type' => $qr->payment_type,
                        'qr_image' => $qr->qr_image,
                        'account_name' => $qr->account_name,
                        'boarding_house' => $qr->boardingHouse->name,
                        'created_by' => $qr->creator->name ?? 'System'
                    ];
                });
            
            return response()->json([
                'status' => 'success',
                'data' => $qrCodes
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching QR codes: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch QR codes'
            ], 500);
        }
    }

    public function storePaymentQRCode(Request $request)
    {
        try {
            $validated = $request->validate([
                'payment_type' => 'required|in:gcash,paymaya',
                'qr_image' => 'required|image|max:2048',
                'account_name' => 'required|string',
                'boarding_house_id' => 'required|exists:boarding_houses,boarding_house_id',
                'created_by' => 'required|exists:users,user_id'
            ]);

            // Get user ID from header
            $userId = $request->header('X-User-Id');
            if (!$userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User ID not provided'
                ], 400);
            }

            // Verify the user is creating for their own boarding house
            $boardingHouse = BoardingHouse::findOrFail($validated['boarding_house_id']);
            if ($boardingHouse->user_id != $userId || $validated['created_by'] != $userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access to this boarding house'
                ], 403);
            }

            $path = $request->file('qr_image')->store('qr-codes', 'public');

            $qrCode = PaymentQrCode::create([
                'payment_type' => $validated['payment_type'],
                'qr_image' => $path,
                'account_name' => $validated['account_name'],
                'boarding_house_id' => $validated['boarding_house_id'],
                'created_by' => $validated['created_by']
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'QR code saved successfully',
                'data' => $qrCode
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving QR code: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deletePaymentQRCode($id)
    {
        try {
            $qrCode = PaymentQrCode::with('boardingHouse')->findOrFail($id);
            
            // Verify ownership
            if ($qrCode->boardingHouse->user_id != auth()->id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to delete this QR code'
                ], 403);
            }

            // Delete the image file
            if ($qrCode->qr_image) {
                Storage::disk('public')->delete($qrCode->qr_image);
            }
            
            $qrCode->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'QR code deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteRoom($id)
    {
        try {
            $room = Room::with('boardingHouse')->findOrFail($id);
            
            // Verify ownership through boarding house
            $userId = request()->header('X-User-Id');
            if (!$userId || !$room->boardingHouse || $room->boardingHouse->user_id != $userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to delete this room'
                ], 403);
            }

            // Delete room image if it exists
            if ($room->room_images) {
                Storage::disk('public')->delete($room->room_images);
            }
            
            $room->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Room deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting room: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete room: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBookingDetails($id)
    {
        try {
            $booking = Booking::with(['room.boardingHouse', 'user'])
                ->where('booking_id', $id)
                ->firstOrFail();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'booking_id' => $booking->booking_id,
                    'room_name' => $booking->room->room_name,
                    'house_name' => $booking->room->boardingHouse->name,
                    'tenant_name' => $booking->user->name,
                    'tenant_email' => $booking->user->email,
                    'check_in_date' => $booking->check_in_date,
                    'check_out_date' => $booking->check_out_date,
                    'status' => $booking->status,
                    'amount' => $booking->room->price,
                    'payment_status' => $booking->payment_status,
                    'room' => [
                        'price' => $booking->room->price
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch booking details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancelBooking($id)
    {
        try {
            $booking = Booking::with(['room', 'user'])->findOrFail($id);
            
            // Verify the booking belongs to one of this landlord's properties
            $userId = request()->header('X-User-Id');
            if (!$userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User ID not provided'
                ], 401);
            }

            $isOwner = Room::where('room_id', $booking->room_id)
                ->whereHas('boardingHouse', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->exists();
            
            Log::info('Booking cancellation attempt', [
                'booking_id' => $id,
                'user_id' => $userId,
                'is_owner' => $isOwner
            ]);
            
            if (!$isOwner) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to cancel this booking'
                ], 403);
            }

            $booking->status = 'cancelled';
            $booking->save();

            // Update room status back to available
            $room = Room::find($booking->room_id);
            if ($room) {
                $room->status = 'available';
                $room->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Booking cancelled successfully',
                'data' => [
                    'user_id' => $booking->user_id,
                    'booking_id' => $booking->booking_id,
                    'room_name' => $booking->room->room_name
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error cancelling booking: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function confirmBooking($id)
    {
        try {
            $booking = Booking::with(['room', 'user'])->findOrFail($id);
            
            // Verify the booking belongs to one of this landlord's properties
            $userId = request()->header('X-User-Id');
            if (!$userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User ID not provided'
                ], 401);
            }

            $isOwner = Room::where('room_id', $booking->room_id)
                ->whereHas('boardingHouse', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->exists();
            
            Log::info('Booking confirmation attempt', [
                'booking_id' => $id,
                'user_id' => $userId,
                'is_owner' => $isOwner
            ]);
            
            if (!$isOwner) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to confirm this booking'
                ], 403);
            }

            $booking->status = 'confirmed';
            $booking->save();

            // Update room status
            $room = Room::find($booking->room_id);
            if ($room) {
                $room->status = 'occupied';
                $room->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Booking confirmed successfully',
                'data' => [
                    'user_id' => $booking->user_id,
                    'booking_id' => $booking->booking_id,
                    'room_name' => $booking->room->room_name
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error confirming booking: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to confirm booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTenants($userId)
    {
        try {
            // Verify user is a landlord
            $user = User::find($userId);
            if (!$user || $user->userType !== 'landlord') {
                throw new \Exception('Unauthorized access');
            }

            // Get boarding houses owned by this landlord
            $boardingHouseIds = BoardingHouse::where('user_id', $userId)
                ->pluck('boarding_house_id');

            // Get rooms from these boarding houses
            $roomIds = Room::whereIn('boarding_house_id', $boardingHouseIds)
                ->pluck('room_id');

            // Get unique tenants who have bookings in these rooms
            $tenants = User::whereHas('bookings', function($query) use ($roomIds) {
                    $query->whereIn('room_id', $roomIds);
                })
                ->with(['bookings' => function($query) use ($roomIds) {
                    $query->whereIn('room_id', $roomIds)
                        ->with(['room' => function($query) {
                            $query->select('room_id', 'room_name');
                        }])
                        ->orderBy('created_at', 'desc');
                }])
                ->select('user_id', 'name', 'email', 'phone_number', 'profile_picture')
                ->get()
                ->map(function($tenant) {
                    $currentBooking = $tenant->bookings->first();
                    
                    return [
                        'user_id' => $tenant->user_id,
                        'name' => $tenant->name,
                        'email' => $tenant->email,
                        'phone_number' => $tenant->phone_number,
                        'profile_picture' => $tenant->profile_picture ? 
                            asset('storage/' . $tenant->profile_picture) : null,
                        'total_bookings' => $tenant->bookings->count(),
                        'current_booking' => $currentBooking ? [
                            'room_name' => $currentBooking->room->room_name,
                            'status' => $currentBooking->status,
                            'check_in_date' => $currentBooking->check_in_date,
                            'check_out_date' => $currentBooking->check_out_date
                        ] : null
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $tenants
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching tenants: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch tenants: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteTenant($userId, $tenantId)
    {
        try {
            // Verify landlord
            $landlord = User::find($userId);
            if (!$landlord || $landlord->userType !== 'landlord') {
                throw new \Exception('Unauthorized access');
            }

            // Begin transaction
            DB::beginTransaction();
            
            try {
                // Get rooms from active bookings to update their status
                $roomIds = Booking::where('user_id', $tenantId)
                    ->where('status', 'confirmed')
                    ->where('check_out_date', '>=', now())
                    ->whereHas('room.boardingHouse', function($query) use ($userId) {
                        $query->where('user_id', $userId);
                    })
                    ->pluck('room_id');

                // Delete tenant's bookings
                Booking::where('user_id', $tenantId)
                    ->whereHas('room.boardingHouse', function($query) use ($userId) {
                        $query->where('user_id', $userId);
                    })
                    ->delete();

                // Update rooms status to available
                if ($roomIds->isNotEmpty()) {
                    Room::whereIn('room_id', $roomIds)->update(['status' => 'available']);
                }

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Tenant records deleted successfully',
                    'updated_room_ids' => $roomIds
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error deleting tenant: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete tenant: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPaymentStats(Request $request)
    {
        try {
            $userId = $request->header('X-User-Id');
            if (!$userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User ID not provided'
                ], 401);
            }

            // Get boarding houses owned by this landlord
            $boardingHouseIds = BoardingHouse::where('user_id', $userId)
                ->pluck('boarding_house_id');

            // Get rooms from these boarding houses
            $roomIds = Room::whereIn('boarding_house_id', $boardingHouseIds)
                ->pluck('room_id');

            // Get all payments for bookings in these rooms
            $payments = Payment::whereHas('booking', function($query) use ($roomIds) {
                    $query->whereIn('room_id', $roomIds);
                });

            // Get the counts directly
            $pendingCount = (clone $payments)->where('status', 'pending')->count();
            $completedCount = (clone $payments)->where('status', 'completed')->count();
            $failedCount = (clone $payments)->where('status', 'failed')->count();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'pending' => $pendingCount,
                    'completed' => $completedCount,
                    'failed' => $failedCount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching payment stats: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch payment stats: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPayments()
    {
        try {
            // Get authenticated user
            if (!auth()->check()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated'
                ], 401);
            }
            
            $userId = auth()->id();
            
            $boardingHouseIds = BoardingHouse::where('user_id', $userId)->pluck('boarding_house_id');
            $roomIds = Room::whereIn('boarding_house_id', $boardingHouseIds)->pluck('room_id');

            // Get payments with necessary relationships
            $payments = Payment::with([
                    'booking.user',
                    'booking.room'
                ])
                ->whereHas('booking', function($query) use ($roomIds) {
                    $query->whereIn('room_id', $roomIds);
                })
                ->get()
                ->map(function($payment) {
                    return [
                        'payment_id' => $payment->payment_id,
                        'booking_id' => $payment->booking_id,
                        'amount' => $payment->status === 'failed' ? null : (float)$payment->amount,
                        'status' => $payment->status,
                        'payment_method' => $payment->payment_method,
                        'reference_number' => $payment->reference_number,
                        'payment_proof' => $payment->payment_proof,
                        'created_at' => $payment->created_at,
                        'booking' => [
                            'user' => [
                                'name' => $payment->booking->user->name ?? 'N/A'
                            ],
                            'room' => [
                                'room_name' => $payment->booking->room->room_name ?? 'N/A',
                                'price' => (float)$payment->booking->room->price,
                                'room_id' => $payment->booking->room->room_id
                            ]
                        ]
                    ];
                });

            // Calculate stats
            $stats = [
                'pending' => $payments->where('status', 'pending')->count(),
                'completed' => $payments->where('status', 'completed')->count(),
                'failed' => $payments->where('status', 'failed')->count()
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'payments' => $payments,
                    'stats' => $stats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch payments'
            ], 500);
        }
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        try {
            $payment = Payment::findOrFail($id);
            
            // Verify that the payment belongs to a booking in one of the landlord's boarding houses
            $landlordId = $request->header('X-User-Id');
            $isLandlordPayment = $payment->booking->room->boardingHouse->user_id == $landlordId;
            
            if (!$isLandlordPayment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to update this payment'
                ], 403);
            }

            $payment->status = $request->status;
            $payment->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment status updated successfully',
                'data' => $payment
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating payment status: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRecentActivities(Request $request)
    {
        try {
            Log::info('Starting getRecentActivities...');
            
            $userId = $request->header('X-User-Id');
            if (!$userId) {
                throw new \Exception('User ID not provided');
            }

            $activities = Activity::with(['performer', 'boardingHouse'])
                ->where(function($query) use ($userId) {
                    $query->where('performed_by', $userId)
                          ->orWhereHas('boardingHouse', function($q) use ($userId) {
                              $q->where('user_id', $userId);
                          });
                })
                ->latest()
                ->take(50)
                ->get()
                ->map(function ($activity) {
                    return [
                        'activity_id' => $activity->activity_id,
                        'type' => $activity->type,
                        'description' => $activity->description,
                        'created_at' => $activity->created_at->diffForHumans(),
                        'timestamp' => $activity->created_at,
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
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch activities: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getActivityIcon($type)
    {
        $icons = [
            'account_created' => 'fas fa-user-plus',
            'account_updated' => 'fas fa-user-edit',
            'boarding_house_created' => 'fas fa-home',
            'boarding_house_updated' => 'fas fa-edit',
            'room_created' => 'fas fa-door-open',
            'room_updated' => 'fas fa-door-closed',
            'booking_created' => 'fas fa-calendar-plus',
            'booking_updated' => 'fas fa-calendar-check',
            'payment_received' => 'fas fa-money-bill-wave',
            'review_added' => 'fas fa-star',
            'default' => 'fas fa-info-circle'
        ];
        return $icons[$type] ?? $icons['default'];
    }

    public function getActivityDescription($activity)
    {
        if (!$activity) return 'Unknown activity';
        
        $description = $activity->description ?? 'No description available';
        
        switch ($activity->type) {
            case 'profile_updated':
                return `Updated profile settings`;
            case 'boarding_house_added':
                return `Added new boarding house: ${description}`;
            case 'boarding_house_updated':
                return `Updated boarding house: ${description}`;
            case 'room_added':
                return `Added new room: ${description}`;
            case 'room_updated':
                return `Updated room: ${description}`;
            case 'booking_added':
                return `New booking received: ${description}`;
            case 'booking_updated':
                return `Updated booking: ${description}`;
            case 'review_received':
                return `Received a new review: ${description}`;
            default:
                return $description;
        }
    }

    public function formatDate($date)
    {
        if (!$date) return 'No date available';
        
        try {
            $options = ['timeZone' => 'Asia/Manila'];
            $now = new \DateTime('now', new \DateTimeZone('Asia/Manila'));
            $activityDate = new \DateTime($date, new \DateTimeZone('Asia/Manila'));
            
            if ($activityDate > $now) {
                return 'Invalid date';
            }
            
            $diffInSeconds = $now->getTimestamp() - $activityDate->getTimestamp();
            $diffInMinutes = floor($diffInSeconds / 60);
            $diffInHours = floor($diffInMinutes / 60);
            $diffInDays = floor($diffInHours / 24);

            if ($diffInSeconds < 60) {
                return 'Just now';
            }
            if ($diffInMinutes < 60) {
                return $diffInMinutes . ' minute' . ($diffInMinutes !== 1 ? 's' : '') . ' ago';
            }
            if ($diffInHours < 24) {
                return $diffInHours . ' hour' . ($diffInHours !== 1 ? 's' : '') . ' ago';
            }
            if ($diffInDays < 7) {
                return $diffInDays . ' day' . ($diffInDays !== 1 ? 's' : '') . ' ago';
            }

            return $activityDate->format('F j, Y g:i A');
        } catch (\Exception $e) {
            Log::error('Error formatting date: ' . $e->getMessage());
            return 'Date error';
        }
    }

    public function deleteReview($id)
    {
        try {
            $userId = request()->header('X-User-Id');
            $review = Review::findOrFail($id);
            
            // Verify the review belongs to one of the landlord's boarding houses
            $isOwner = BoardingHouse::where('boarding_house_id', $review->boarding_house_id)
                ->where('user_id', $userId)
                ->exists();
            
            if (!$isOwner) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to delete this review'
                ], 403);
            }

            $review->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Review deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting review: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete review: ' . $e->getMessage()
            ], 500);
        }
    }
} 