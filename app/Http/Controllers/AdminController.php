<?php

// AdminController.php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\BoardingHouse;
use App\Models\Room;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Support;
use App\Models\PaymentQrCode;
use App\Models\Notification;
use App\Models\Activity;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Traits\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    use ActivityLogger;

    // Fetch all accounts
    public function fetchAccounts()
    {
        try {
            Log::info('Starting fetchAccounts...');
            
            // Get all types of accounts with creator and updater information
            $accounts = User::select(
                'users.*',
                'creator.name as created_by_name',
                'creator.userType as created_by_type',
                'updater.name as updated_by_name',
                'updater.userType as updated_by_type'
            )
            ->leftJoin('users as creator', 'users.created_by', '=', 'creator.user_id')
            ->leftJoin('users as updater', 'users.updated_by', '=', 'updater.user_id')
            ->get();

            Log::info('Raw accounts:', ['accounts' => $accounts->toArray()]);

            // Transform the profile_picture URLs
            $accounts = $accounts->map(function ($account) {
                if ($account->profile_picture) {
                    $profilePath = str_replace(['public/', 'storage/'], '', $account->profile_picture);
                    if (Storage::disk('public')->exists($profilePath)) {
                        $account->profile_picture = asset('storage/' . $profilePath);
                        Log::info('Profile URL created:', ['url' => $account->profile_picture]);
                    } else {
                        $account->profile_picture = null;
                        Log::warning('Profile picture file not found:', ['path' => $profilePath]);
                    }
                }
                return $account;
            });

            // Group accounts by userType
            $groupedAccounts = [
                'user' => $accounts->where('userType', 'user')->values(),
                'landlord' => $accounts->where('userType', 'landlord')->values(),
                'admin' => $accounts->where('userType', 'admin')->values()
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => $groupedAccounts
            ]);

        } catch (\Exception $e) {
            Log::error('Error in fetchAccounts: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch accounts: ' . $e->getMessage()
            ], 500);
        }
    }

    // Delete account
    public function deleteAccount($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent deletion of admin accounts
            if ($user->userType === 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Admin accounts cannot be deleted'
                ], 403);
            }
            
            // Only allow deletion of user and landlord accounts
            if (!in_array($user->userType, ['user', 'landlord'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid account type for deletion'
                ], 403);
            }
            
            $user->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Account deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting account: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete account: ' . $e->getMessage()
            ], 500);
        }
    }

    // Edit account
    public function editAccount($id)
    {
        try {
            $user = User::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => [
                    'user_id' => $user->user_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'gender' => $user->gender,
                    'birthdate' => $user->birthdate,
                    'profile_picture' => $user->profile_picture,
                    'userType' => $user->userType,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch account details'
            ], 500);
        }
    }

    // Update account
    public function updateAccount(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id . ',user_id',
                'phone_number' => 'nullable|string|max:20',
                'gender' => 'nullable|string|in:male,female,other',
                'birthdate' => 'nullable|date',
                'userType' => 'required|string|in:user,landlord,admin',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'updated_by' => 'required|exists:users,user_id'
            ]);

            $user = User::findOrFail($id);

            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture if exists
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
                $profilePicturePath = $request->file('profile_picture')->store('profile-pictures', 'public');
                $validatedData['profile_picture'] = $profilePicturePath;
            }

            // Set updated_by to the current admin's ID
            $validatedData['updated_by'] = $request->updated_by;

            $user->update($validatedData);

            // Get the updated user with creator/updater info
            $updatedUser = User::select(
                'users.*',
                'creator.name as created_by_name',
                'creator.userType as created_by_type',
                'updater.name as updated_by_name',
                'updater.userType as updated_by_type'
            )
            ->leftJoin('users as creator', 'users.created_by', '=', 'creator.user_id')
            ->leftJoin('users as updater', 'users.updated_by', '=', 'updater.user_id')
            ->where('users.user_id', $id)
            ->first();

            // Log activity
            $this->logActivity(
                'account_updated',
                "Updated {$user->userType} account for {$user->name}",
                $user->user_id
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Account updated successfully',
                'data' => $updatedUser
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating account: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update account: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchAccount($id)
    {
        try {
            Log::info('Fetching account for ID: ' . $id);
            
            if (!$id) {
                Log::error('Invalid user ID provided');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid user ID'
                ], 400);
            }
            
            $user = User::findOrFail($id);
            Log::info('Found user:', ['user' => $user->toArray()]);
            
            // Transform profile picture URL if exists
            if ($user->profile_picture) {
                $profilePath = str_replace(['public/', 'storage/'], '', $user->profile_picture);
                
                if (Storage::disk('public')->exists($profilePath)) {
                    $user->profile_picture = asset('storage/' . $profilePath);
                    Log::info('Profile picture URL:', ['url' => $user->profile_picture]);
                } else {
                    Log::warning('Profile picture file not found:', ['path' => $profilePath]);
                    $user->profile_picture = null;
                }
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching account: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch account details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addUser(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'userType' => 'required|in:admin,landlord,user',
                'created_by' => 'required|exists:users,user_id',
                'updated_by' => 'required|exists:users,user_id'
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'userType' => $validated['userType'],
                'created_by' => $validated['created_by'],
                'updated_by' => $validated['updated_by']
            ]);

            // Get the admin who created the account
            $admin = User::find($validated['created_by']);

            // Create activity with proper performer information
            Activity::create([
                'type' => 'account_created',
                'description' => "Created new {$user->userType} account for {$user->name}",
                'performed_by' => $admin->user_id,
                'created_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create user: ' . $e->getMessage()
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
                'bh_images' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'user_id' => 'required|exists:users,user_id',
                'created_by' => 'required|exists:users,user_id'
            ]);

            $userId = auth()->id() ?? $request->input('created_by');
            
            if (!$userId) {
                throw new \Exception('User not authenticated');
            }

            if ($request->hasFile('bh_images')) {
                $imagePath = $request->file('bh_images')->store('boarding-houses', 'public');
                $validatedData['bh_images'] = $imagePath;
            }

            // Add updated_by field with the same value as created_by
            $validatedData['updated_by'] = $userId;

            $boardingHouse = BoardingHouse::create($validatedData);

            // Create activity with explicit performer_id
            Activity::create([
                'type' => 'boarding_house_created',
                'description' => "Added new boarding house {$boardingHouse->name}",
                'user_id' => $userId,
                'performed_by' => $userId  // Changed from performer_id to performed_by
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $boardingHouse
            ]);
        } catch (\Exception $e) {
            Log::error('Error adding boarding house: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add boarding house: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchBoardingHouses()
    {
        try {
            Log::info('Fetching boarding houses');
            
            $boardingHouses = BoardingHouse::select(
                'boarding_house_id',
                'name',
                'description',
                'address'
            )
            ->orderBy('name')
            ->get();

            Log::info('Found boarding houses:', ['count' => $boardingHouses->count()]);
            
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

    public function addRoom(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'boarding_house_id' => 'required|exists:boarding_houses,boarding_house_id',
                'room_name' => 'required|string|max:255',
                'bed_quantity' => 'required|integer|min:1',
                'status' => 'required|in:available,occupied,maintenance',
                'room_images' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'map_link' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'created_by' => 'required|exists:users,user_id'
            ]);

            // Get authenticated user ID
            $userId = auth()->id() ?? $request->input('created_by');
            
            if (!$userId) {
                throw new \Exception('User not authenticated');
            }

            // Handle room image upload
            if ($request->hasFile('room_images')) {
                $imagePath = $request->file('room_images')->store('room-images', 'public');
                $validatedData['room_images'] = $imagePath;
            }

            // Add required fields
            $validatedData['user_id'] = $userId;
            $validatedData['created_by'] = $userId;
            $validatedData['updated_by'] = $userId;

            // Create the room
            $room = Room::create($validatedData);

            // Get boarding house name for activity description
            $boardingHouse = BoardingHouse::find($validatedData['boarding_house_id']);
            $boardingHouseName = $boardingHouse ? $boardingHouse->name : 'Unknown Boarding House';

            // Add activity log
            Activity::create([
                'type' => 'room_created',
                'description' => "Added new room {$room->room_name} to {$boardingHouseName}",
                'user_id' => $userId,
                'performed_by' => $userId  // Changed from performer_id to performed_by
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Room added successfully',
                'data' => $room
            ]);

        } catch (\Exception $e) {
            \Log::error('Error adding room: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add room: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchRooms()
    {
        try {
            Log::info('Starting fetchRooms...');
            
            $rooms = \App\Models\Room::select(
                'rooms.*',
                'creator.name as created_by_name',
                'creator.userType as created_by_type',
                'updater.name as updated_by_name',
                'updater.userType as updated_by_type',
                'boarding_houses.name as boarding_house_name'
            )
            ->leftJoin('users as creator', 'rooms.created_by', '=', 'creator.user_id')
            ->leftJoin('users as updater', 'rooms.updated_by', '=', 'updater.user_id')
            ->leftJoin('boarding_houses', 'rooms.boarding_house_id', '=', 'boarding_houses.boarding_house_id')
            ->orderBy('rooms.created_at', 'desc')
            ->get();

            return response()->json([
                'status' => 'success',
                'data' => $rooms
            ]);

        } catch (\Exception $e) {
            Log::error('Error in fetchRooms: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch rooms: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchBoardingHouse($id)
    {
        try {
            $boardingHouse = \App\Models\BoardingHouse::findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'data' => $boardingHouse
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch boarding house: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateBoardingHouse(Request $request, $id)
    {
        try {
            $boardingHouse = BoardingHouse::findOrFail($id);
            
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'address' => 'required|string',
                'landlord_name' => 'required|string|max:255',
                'landlord_phone' => 'required|string|max:20',
                'bh_images' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($request->hasFile('bh_images')) {
                // Delete old image if exists
                if ($boardingHouse->bh_images) {
                    Storage::disk('public')->delete($boardingHouse->bh_images);
                }
                $imagePath = $request->file('bh_images')->store('boarding-houses', 'public');
                $validatedData['bh_images'] = $imagePath;
            }

            $boardingHouse->update($validatedData);

            // Log activity
            $this->logActivity(
                'boarding_house_updated',
                "Updated boarding house {$boardingHouse->name}",
                $boardingHouse->user_id
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Boarding house updated successfully',
                'data' => $boardingHouse
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating boarding house: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update boarding house: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchRoom($id)
    {
        try {
            $room = Room::with('boardingHouse')->findOrFail($id);
            
            // Transform room image URL if exists
            $roomImageUrl = null;
            if ($room->room_images) {
                // Remove 'public/' from the start if it exists
                $imagePath = str_replace('public/', '', $room->room_images);
                
                // Check if file exists and create full URL
                if (Storage::disk('public')->exists($imagePath)) {
                    $roomImageUrl = asset('storage/' . $imagePath);
                    Log::info('Room image URL created:', ['url' => $roomImageUrl]);
                } else {
                    Log::warning('Room image file not found:', ['path' => $imagePath]);
                }
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
                    'boarding_house_name' => $room->boardingHouse->name ?? 'Unknown'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching room: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch room: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateRoom(Request $request, $id)
    {
        try {
            $room = Room::findOrFail($id);

            $validatedData = $request->validate([
                'room_name' => 'required|string|max:255',
                'bed_quantity' => 'required|integer|min:1',
                'status' => 'required|in:available,occupied,maintenance',
                'room_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'map_link' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'boarding_house_id' => 'required|exists:boarding_houses,boarding_house_id',
                'updated_by' => 'required|exists:users,user_id'
            ]);

            // Handle image upload if new image is provided
            if ($request->hasFile('room_image')) {
                // Delete old image if it exists
                if ($room->room_images) {
                    Storage::disk('public')->delete($room->room_images);
                }
                
                // Store new image
                $imagePath = $request->file('room_image')->store('room-images', 'public');
                $validatedData['room_images'] = $imagePath;
            }

            $room->update($validatedData);

            // Log activity
            $this->logActivity(
                'room_updated',
                "Updated room {$room->room_name} in {$room->boardingHouse->name}",
                $room->user_id
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Room updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating room: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update room: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id . ',user_id',
                'phone_number' => 'nullable|string|max:20',
                'gender' => 'nullable|string|in:male,female,other',
                'birthdate' => 'nullable|date',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $user = User::findOrFail($id);

            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture if exists
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
                
                // Store new profile picture
                $validatedData['profile_picture'] = $request->file('profile_picture')
                    ->store('profile-pictures', 'public');
            }

            $user->update($validatedData);

            // Transform profile picture URL if exists
            if ($user->profile_picture) {
                $user->profile_picture = url('storage/' . $user->profile_picture);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Clear session data
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error during logout: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBoardingHouses()
    {
        try {
            $boardingHouses = BoardingHouse::select(
                'boarding_houses.*',
                'creator.name as created_by_name',
                'creator.userType as created_by_type',
                'updater.name as updated_by_name',
                'updater.userType as updated_by_type'
            )
            ->leftJoin('users as creator', 'boarding_houses.created_by', '=', 'creator.user_id')
            ->leftJoin('users as updater', 'boarding_houses.updated_by', '=', 'updater.user_id')
            ->withCount([
                'rooms',
                'rooms as available_rooms_count' => function ($query) {
                    $query->where('status', 'available');
                },
                'rooms as occupied_rooms_count' => function ($query) {
                    $query->where('status', 'occupied');
                },
                'rooms as maintenance_rooms_count' => function ($query) {
                    $query->where('status', 'maintenance');
                }
            ])
            ->get()
            ->map(function($house) {
                return [
                    'boarding_house_id' => $house->boarding_house_id,
                    'name' => $house->name,
                    'description' => $house->description,
                    'address' => $house->address,
                    'landlord_name' => $house->landlord_name,
                    'landlord_phone' => $house->landlord_phone,
                    'bh_images' => $house->bh_images ? asset('storage/' . $house->bh_images) : null,
                    'created_at' => $house->created_at,
                    'updated_at' => $house->updated_at,
                    'created_by_name' => $house->created_by_name,
                    'created_by_type' => $house->created_by_type,
                    'updated_by_name' => $house->updated_by_name,
                    'updated_by_type' => $house->updated_by_type,
                    'rooms_count' => $house->rooms_count,
                    'available_rooms_count' => $house->available_rooms_count,
                    'occupied_rooms_count' => $house->occupied_rooms_count,
                    'maintenance_rooms_count' => $house->maintenance_rooms_count
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

    public function getBoardingHouse($id)
    {
        try {
            Log::info('Fetching boarding house with ID: ' . $id);
            
            if (!$id) {
                Log::error('Invalid boarding house ID provided');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid boarding house ID'
                ], 400);
            }

            $boardingHouse = \App\Models\BoardingHouse::select(
                'boarding_houses.*',
                'creator.name as created_by_name',
                'creator.userType as created_by_type',
                'updater.name as updated_by_name',
                'updater.userType as updated_by_type'
            )
            ->leftJoin('users as creator', 'boarding_houses.created_by', '=', 'creator.user_id')
            ->leftJoin('users as updater', 'boarding_houses.updated_by', '=', 'updater.user_id')
            ->where('boarding_houses.boarding_house_id', $id)
            ->first();

            if (!$boardingHouse) {
                Log::error('Boarding house not found with ID: ' . $id);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Boarding house not found'
                ], 404);
            }

            // Get room counts
            $roomCounts = \App\Models\Room::where('boarding_house_id', $id)
                ->selectRaw('
                    COUNT(*) as total_rooms,
                    SUM(CASE WHEN status = "available" THEN 1 ELSE 0 END) as available_rooms,
                    SUM(CASE WHEN status = "occupied" THEN 1 ELSE 0 END) as occupied_rooms
                ')
                ->first();

            $boardingHouse->rooms = $roomCounts->total_rooms ?? 0;
            $boardingHouse->available = $roomCounts->available_rooms ?? 0;
            $boardingHouse->occupied = $roomCounts->occupied_rooms ?? 0;

            Log::info('Found boarding house:', ['boarding_house' => $boardingHouse->toArray()]);

            return response()->json([
                'status' => 'success',
                'data' => $boardingHouse
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching boarding house: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch boarding house: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRooms()
    {
        try {
            Log::info('Starting getRooms...');
            
            $rooms = \App\Models\Room::select(
                'rooms.*',
                'boarding_houses.name as boarding_house_name',
                'boarding_houses.address as boarding_house_address',
                'creator.name as created_by_name',
                'creator.userType as created_by_type',
                'updater.name as updated_by_name',
                'updater.userType as updated_by_type'
            )
            ->leftJoin('boarding_houses', 'rooms.boarding_house_id', '=', 'boarding_houses.boarding_house_id')
            ->leftJoin('users as creator', 'rooms.created_by', '=', 'creator.user_id')
            ->leftJoin('users as updater', 'rooms.updated_by', '=', 'updater.user_id')
            ->orderBy('rooms.created_at', 'desc')
            ->get();

            // Transform room image URLs
            $rooms = $rooms->map(function ($room) {
                if ($room->room_images) {
                    $imagePath = str_replace(['public/', 'storage/'], '', $room->room_images);
                    
                    if (Storage::disk('public')->exists($imagePath)) {
                        $room->room_images = asset('storage/' . $imagePath);
                        Log::info('Room image URL created:', ['url' => $room->room_images]);
                    } else {
                        Log::warning('Room image not found:', ['path' => $imagePath]);
                        $room->room_images = asset('images/room-placeholder.jpg');
                    }
                }
                return $room;
            });

            return response()->json([
                'status' => 'success',
                'data' => $rooms
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getRooms: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch rooms: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDashboardData()
    {
        try {
            // Get total accounts
            $totalAccounts = User::count();
            
            // Get total boarding houses
            $totalBoardingHouses = BoardingHouse::count();
            
            // Get booking statistics
            $bookingStats = [
                'pending' => Booking::where('status', 'pending')->count(),
                'confirmed' => Booking::where('status', 'confirmed')->count(),
                'cancelled' => Booking::where('status', 'cancelled')->count()
            ];
            
            // Calculate total revenue from completed payments
            $totalRevenue = Payment::where('status', 'completed')
                ->sum('amount');

            // Get payment statistics
            $paymentStats = [
                'pending' => Payment::where('status', 'pending')->count(),
                'completed' => Payment::where('status', 'completed')->count(),
                'failed' => Payment::where('status', 'failed')->count()
            ];

            // Get room statistics
            $roomStats = [
                'available' => Room::where('status', 'available')->count(),
                'occupied' => Room::where('status', 'occupied')->count(),
                'maintenance' => Room::where('status', 'maintenance')->count()
            ];

            // Get tenant statistics
            $tenantStats = [
                'total' => User::where('userType', 'user')->count(),
                'active' => Booking::where('status', 'confirmed')
                    ->distinct('user_id')
                    ->count('user_id')
            ];

            // Get review statistics
            $reviews = Review::all();
            $reviewStats = [
                'total' => $reviews->count(),
                'averageRating' => round($reviews->avg('rating') ?? 0, 1), // Round to 1 decimal place
                'positiveReviews' => $reviews->where('rating', '>=', 4)->count(),
                'negativeReviews' => $reviews->where('rating', '<', 3)->count()
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'totalAccounts' => $totalAccounts,
                    'totalBoardingHouses' => $totalBoardingHouses,
                    'bookingStats' => $bookingStats,
                    'totalRevenue' => $totalRevenue,
                    'paymentStats' => $paymentStats,
                    'roomStats' => $roomStats,
                    'tenantStats' => $tenantStats,
                    'reviewStats' => $reviewStats,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getDashboardData: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch dashboard data'
            ], 500);
        }
    }

    public function getRevenue()
    {
        try {
            Log::info('Fetching revenue data...');

            // Get all payments with related data
            $payments = Payment::with([
                'booking.room.boardingHouse',
                'booking.user'
            ])
            ->whereIn('status', ['completed', 'confirmed'])
            ->get()
            ->map(function($payment) {
                return [
                    'id' => $payment->payment_id,
                    'date' => $payment->created_at,
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                    'boarding_house_id' => $payment->booking->room->boardingHouse->boarding_house_id ?? null,
                    'boarding_house_name' => $payment->booking->room->boardingHouse->name ?? 'Unknown',
                    'room_name' => $payment->booking->room->room_name ?? 'Unknown',
                    'guest_name' => $payment->booking->user->name ?? 'Unknown',
                    'check_in_date' => $payment->booking->check_in_date,
                    'check_out_date' => $payment->booking->check_out_date,
                    'duration' => Carbon::parse($payment->booking->check_in_date)
                        ->diffInDays(Carbon::parse($payment->booking->check_out_date))
                ];
            });

            $revenueData = [
                'bookingStats' => [
                    'total' => $payments->count(),
                    'confirmed' => $payments->where('status', 'confirmed')->count(),
                    'pending' => Payment::where('status', 'pending')->count(),
                    'cancelled' => Payment::where('status', 'cancelled')->count()
                ],
                'revenue' => [
                    'total' => $payments->sum('amount'),
                    'pending' => Payment::where('status', 'pending')->sum('amount')
                ],
                'payments' => $payments
            ];

            return response()->json([
                'status' => 'success',
                'data' => $revenueData
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching revenue data: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch revenue data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBookings()
    {
        try {
            $bookings = Booking::with(['user', 'room.boardingHouse', 'payments'])
                ->get()
                ->map(function ($booking) {
                    // Calculate total paid amount
                    $totalPaid = $booking->payments
                        ->whereIn('status', ['completed', 'confirmed'])
                        ->sum('amount');

                    return [
                        'id' => $booking->booking_id,
                        'booking_id' => $booking->booking_id, // Add this for consistency
                        'status' => $booking->status,
                        'check_in_date' => $booking->check_in_date,
                        'check_out_date' => $booking->check_out_date,
                        'total_paid' => $totalPaid,
                        'user' => [
                            'name' => $booking->user->name,
                            'email' => $booking->user->email,
                            'phone' => $booking->user->phone_number,
                            'gender' => $booking->user->gender,
                            'profile_picture' => $booking->user->profile_picture
                        ],
                        'room' => [
                            'id' => $booking->room->room_id,
                            'name' => $booking->room->room_name, // Changed from name to room_name
                            'room_name' => $booking->room->room_name, // Add this for consistency
                            'price' => $booking->room->price,
                            'boarding_house' => $booking->room->boardingHouse->name,
                            'boarding_house_id' => $booking->room->boarding_house_id // Add this for filtering
                        ],
                        'payments' => $booking->payments->map(function ($payment) {
                            return [
                                'payment_id' => $payment->payment_id,
                                'amount' => $payment->amount,
                                'status' => $payment->status,
                                'payment_method' => $payment->payment_method, // Add payment method
                                'payment_proof' => $payment->payment_proof ? asset('storage/' . $payment->payment_proof) : null,
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
            \Log::error('Error in getBookings: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch bookings'
            ], 500);
        }
    }

    public function getTotalReviews()
    {
        try {
            $reviewStats = \App\Models\Review::selectRaw('
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating,
                COUNT(CASE WHEN rating >= 4 THEN 1 END) as positive_reviews,
                COUNT(CASE WHEN rating <= 2 THEN 1 END) as negative_reviews
            ')->first();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total' => $reviewStats->total_reviews ?? 0,
                    'averageRating' => round($reviewStats->average_rating ?? 0, 1),
                    'positiveReviews' => $reviewStats->positive_reviews ?? 0,
                    'negativeReviews' => $reviewStats->negative_reviews ?? 0
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting review stats: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get review statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getLandlordDashboardData($userId)
    {
        try {
            Log::info('Fetching landlord dashboard data for user ID: ' . $userId);

            // Get landlord user data first
            $landlord = User::findOrFail($userId);
            if ($landlord->userType !== 'landlord') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User is not a landlord'
                ], 403);
            }

            // Get boarding houses owned by this landlord
            $boardingHouses = BoardingHouse::where('user_id', $userId)
                ->withCount([
                    'rooms',
                    'rooms as available_rooms_count' => function ($query) {
                        $query->where('status', 'available');
                    },
                    'rooms as occupied_rooms_count' => function ($query) {
                        $query->where('status', 'occupied');
                    },
                    'rooms as maintenance_rooms_count' => function ($query) {
                        $query->where('status', 'maintenance');
                    }
                ])
                ->get();

            // Get total rooms and their status counts
            $roomStats = Room::whereIn('boarding_house_id', $boardingHouses->pluck('boarding_house_id'))
                ->selectRaw('
                    COUNT(*) as total_rooms,
                    SUM(CASE WHEN status = "available" THEN 1 ELSE 0 END) as available_rooms,
                    SUM(CASE WHEN status = "occupied" THEN 1 ELSE 0 END) as occupied_rooms,
                    SUM(CASE WHEN status = "maintenance" THEN 1 ELSE 0 END) as maintenance_rooms
                ')
                ->first();

            // Get bookings for this landlord's rooms
            $bookings = Booking::whereIn('room_id', 
                Room::whereIn('boarding_house_id', $boardingHouses->pluck('boarding_house_id'))
                    ->pluck('room_id')
            )
            ->with(['user:user_id,name,email,phone_number']) // Include user details
            ->get();

            // Get reviews for this landlord's boarding houses
            $reviews = Review::whereIn('boarding_house_id', $boardingHouses->pluck('boarding_house_id'))
                ->with(['user:user_id,name']) // Include reviewer details
                ->count();

            // Calculate monthly revenue
            $monthlyRevenue = Booking::whereIn('room_id', 
                Room::whereIn('boarding_house_id', $boardingHouses->pluck('boarding_house_id'))
                    ->pluck('room_id')
            )
            ->where('status', 'confirmed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->join('rooms', 'bookings.room_id', '=', 'rooms.room_id')
            ->join('boarding_houses', 'rooms.boarding_house_id', '=', 'boarding_houses.boarding_house_id')
            ->sum(DB::raw('boarding_houses.price * DATEDIFF(bookings.check_out_date, bookings.check_in_date)'));

            // Get recent activities
            $recentActivities = collect();

            // Add boarding house activities
            $boardingHouseActivities = BoardingHouse::where('user_id', $userId)
                ->orderBy('updated_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($house) use ($landlord) {
                    $isNew = $house->created_at == $house->updated_at;
                    return [
                        'id' => $house->boarding_house_id,
                        'type' => 'boarding_house',
                        'action' => $isNew ? 'added' : 'updated',
                        'description' => ($isNew ? 'Added new' : 'Updated') . " boarding house: {$house->name}",
                        'created_at' => $house->created_at,
                        'updated_at' => $house->updated_at,
                        'user_name' => $landlord->name
                    ];
                });
            $recentActivities = $recentActivities->concat($boardingHouseActivities);

            // Add room activities
            $roomActivities = Room::whereIn('boarding_house_id', $boardingHouses->pluck('boarding_house_id'))
                ->orderBy('updated_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($room) use ($landlord) {
                    $isNew = $room->created_at == $room->updated_at;
                    return [
                        'id' => $room->room_id,
                        'type' => 'room',
                        'action' => $isNew ? 'added' : 'updated',
                        'description' => ($isNew ? 'Added new' : 'Updated') . " room: {$room->room_name}",
                        'created_at' => $room->created_at,
                        'updated_at' => $room->updated_at,
                        'user_name' => $landlord->name
                    ];
                });
            $recentActivities = $recentActivities->concat($roomActivities);

            // Sort activities by date
            $recentActivities = $recentActivities->sortByDesc('updated_at')->take(10);

            $dashboardData = [
                'landlord' => [
                    'name' => $landlord->name,
                    'email' => $landlord->email,
                    'phone_number' => $landlord->phone_number,
                    'profile_picture' => $landlord->profile_picture
                ],
                'totalBoardingHouses' => $boardingHouses->count(),
                'roomStats' => [
                    'total' => $roomStats->total_rooms ?? 0,
                    'available' => $roomStats->available_rooms ?? 0,
                    'occupied' => $roomStats->occupied_rooms ?? 0,
                    'maintenance' => $roomStats->maintenance_rooms ?? 0
                ],
                'totalBookings' => $bookings->count(),
                'totalReviews' => $reviews,
                'monthlyRevenue' => $monthlyRevenue,
                'recentActivities' => $recentActivities,
                'boardingHouses' => $boardingHouses,
                'bookings' => $bookings->map(function ($booking) {
                    return [
                        'id' => $booking->booking_id,
                        'user' => $booking->user,
                        'check_in_date' => $booking->check_in_date,
                        'check_out_date' => $booking->check_out_date,
                        'status' => $booking->status,
                        'created_at' => $booking->created_at
                    ];
                })
            ];

            return response()->json([
                'status' => 'success',
                'data' => $dashboardData
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching landlord dashboard data: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSupportRequests()
    {
        try {
            Log::info('Fetching support requests');
            
            $requests = Support::with('user')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($request) {
                    // Get profile picture URL if it exists
                    $profilePicture = $request->user && $request->user->profile_picture 
                        ? Storage::disk('public')->exists($request->user->profile_picture)
                            ? asset('storage/' . $request->user->profile_picture)
                            : null
                        : null;

                    return [
                        'support_id' => $request->support_id,
                        'subject' => $request->subject,
                        'description' => $request->description,
                        'priority' => $request->priority,
                        'status' => $request->status,
                        'user_id' => $request->user_id,
                        'user_name' => $request->user ? $request->user->name : 'Unknown User',
                        'user_profile_picture' => $profilePicture,
                        'created_at' => $request->created_at,
                        'updated_at' => $request->updated_at
                    ];
                });

            // Calculate stats
            $stats = [
                'total' => $requests->count(),
                'pending' => $requests->where('status', 'pending')->count(),
                'inProgress' => $requests->where('status', 'in_progress')->count(),
                'resolved' => $requests->where('status', 'resolved')->count()
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'requests' => $requests,
                    'stats' => $stats
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching support requests: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch support requests: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateSupportStatus(Request $request, $id)
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

    public function respondToSupport(Request $request, $id)
    {
        try {
            Log::info('Responding to support request:', [
                'id' => $id,
                'request_data' => $request->all()
            ]);

            $validatedData = $request->validate([
                'response' => 'required|string',
                'status' => 'required|in:pending,in_progress,resolved'
            ]);

            $supportRequest = Support::with('user')->findOrFail($id);
            Log::info('Found support request:', ['support_request' => $supportRequest->toArray()]);
            
            // Update support request status
            $supportRequest->status = $validatedData['status'];
            $supportRequest->save();

            // Create notification for the specific user
            $notification = Notification::create([
                'user_id' => $supportRequest->user_id,
                'type' => 'support_response',
                'message' => "Admin response to '{$supportRequest->subject}': {$validatedData['response']}",
                'link' => '/user/help',
                'read' => false
            ]);

            Log::info('Created notification:', ['notification' => $notification->toArray()]);

            return response()->json([
                'status' => 'success',
                'message' => 'Response sent successfully',
                'data' => [
                    'support_request' => $supportRequest,
                    'user_id' => $supportRequest->user_id,
                    'notification' => $notification
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error responding to support request: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send response: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteSupportRequest($id)
    {
        try {
            Log::info('Attempting to delete support request:', ['id' => $id]);
            
            $supportRequest = Support::findOrFail($id);
            
            // Only allow deletion if status is resolved
            if ($supportRequest->status !== 'resolved') {
                Log::warning('Attempted to delete unresolved support request:', ['id' => $id, 'status' => $supportRequest->status]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Only resolved support requests can be deleted'
                ], 400);
            }

            $supportRequest->delete();
            Log::info('Support request deleted successfully:', ['id' => $id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Support request deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting support request: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete support request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteBoardingHouse($id)
    {
        try {
            $boardingHouse = BoardingHouse::findOrFail($id);
            
            // Delete the image if it exists
            if ($boardingHouse->bh_images) {
                Storage::disk('public')->delete($boardingHouse->bh_images);
            }
            
            // Delete the boarding house
            $boardingHouse->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Boarding house deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting boarding house: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete boarding house: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPaymentQRCodes()
    {
        try {
            // For admin, get all QR codes
            $qrCodes = PaymentQrCode::with(['boardingHouse', 'creator'])
                ->get()
                ->map(function($qr) {
                    return [
                        'qr_id' => $qr->qr_id,
                        'payment_type' => $qr->payment_type,
                        'qr_image' => $qr->qr_image,
                        'account_name' => $qr->account_name,
                        'account_number' => $qr->account_number,
                        'boarding_house' => $qr->boardingHouse->name,
                        'created_by' => $qr->creator->name
                    ];
                });
            
            return response()->json([
                'status' => 'success',
                'data' => $qrCodes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch QR codes: ' . $e->getMessage()
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
            $qrCode = PaymentQrCode::findOrFail($id);
            
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
                'message' => 'Failed to delete QR code'
            ], 500);
        }
    }

    public function confirmBooking($bookingId)
    {
        try {
            $booking = \App\Models\Booking::findOrFail($bookingId);

            if ($booking->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This booking cannot be confirmed because its current status is: ' . $booking->status
                ], 400);
            }

            // Update the room status to occupied
            $room = \App\Models\Room::findOrFail($booking->room_id);
            $room->status = 'occupied';
            $room->save();

            $booking->status = 'confirmed';
            $booking->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Booking confirmed successfully',
                'data' => [
                    'user_id' => $booking->user_id,
                    'booking_id' => $booking->booking_id,
                    'room_id' => $room->room_id,
                    'room_status' => $room->status
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error confirming booking: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to confirm booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancelBooking($bookingId) 
    {
        try {
            $booking = \App\Models\Booking::findOrFail($bookingId);

            if ($booking->status === 'cancelled') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This booking is already cancelled'
                ], 400);
            }

            // Update the room status back to available
            $room = \App\Models\Room::findOrFail($booking->room_id);
            $room->status = 'available';
            $room->save();

            $booking->status = 'cancelled';
            $booking->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Booking cancelled successfully',
                'data' => [
                    'user_id' => $booking->user_id,
                    'booking_id' => $booking->booking_id,
                    'room_id' => $room->room_id,
                    'room_status' => $room->status
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error cancelling booking: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteRoom($id)
    {
        try {
            $room = Room::findOrFail($id);
            
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

    public function getTenantStats()
    {
        try {
            // Get users who have made bookings (tenants)
            $totalTenants = User::whereHas('bookings')->count();
            
            // Get users with active bookings
            $activeTenants = User::whereHas('bookings', function($query) {
                $query->where('status', 'confirmed')
                      ->where('check_out_date', '>=', now());
            })->count();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total' => $totalTenants,
                    'active' => $activeTenants
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching tenant stats: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch tenant statistics'
            ], 500);
        }
    }

    public function getTenants()
    {
        try {
            // Get all users who have bookings (tenants)
            $tenants = User::whereHas('bookings')
                ->with(['bookings' => function($query) {
                    $query->with(['room.boardingHouse'])
                        ->orderBy('created_at', 'desc');
                }])
                ->get()
                ->map(function($tenant) {
                    // Get the active booking (if any)
                    $activeBooking = $tenant->bookings
                        ->where('status', 'confirmed')
                        ->where('check_out_date', '>=', now())
                        ->first();

                    // Ensure profile picture URL is properly formatted
                    $profilePicture = $tenant->profile_picture 
                        ? Storage::disk('public')->exists($tenant->profile_picture)
                            ? asset('storage/' . $tenant->profile_picture)
                            : null
                        : null;

                    return [
                        'id' => $tenant->user_id,
                        'name' => $tenant->name,
                        'email' => $tenant->email,
                        'phone' => $tenant->phone_number,
                        'gender' => $tenant->gender,
                        'profile_picture' => $profilePicture,
                        'total_bookings' => $tenant->bookings->count(),
                        'is_active' => !is_null($activeBooking),
                        'active_booking' => $activeBooking ? [
                            'room' => $activeBooking->room->room_name,
                            'boarding_house' => $activeBooking->room->boardingHouse->name,
                            'check_in' => $activeBooking->check_in_date,
                            'check_out' => $activeBooking->check_out_date
                        ] : null
                    ];
                });

            // Calculate active tenants count
            $activeTenants = $tenants->filter(function($tenant) {
                return $tenant['is_active'];
            })->count();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'tenants' => $tenants,
                    'stats' => [
                        'total' => $tenants->count(),
                        'active' => $activeTenants
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching tenants: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch tenants'
            ], 500);
        }
    }

    public function deleteTenant($id)
    {
        try {
            $tenant = User::findOrFail($id);
            
            // Check if user is a tenant (has bookings)
            if (!$tenant->bookings()->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This user is not a tenant'
                ], 400);
            }

            // Begin transaction
            DB::beginTransaction();
            
            try {
                // Get rooms from active bookings to update their status
                $roomIds = $tenant->bookings()
                    ->where('status', 'confirmed')
                    ->where('check_out_date', '>=', now())
                    ->pluck('room_id');
                
                // Delete related reviews for the bookings
                Review::whereIn('booking_id', $tenant->bookings()->pluck('booking_id'))->delete();
                
                // Delete related payments for the bookings
                Payment::whereIn('booking_id', $tenant->bookings()->pluck('booking_id'))->delete();
                
                // Delete the bookings
                $tenant->bookings()->delete();
                
                // Update rooms status to available
                if ($roomIds->isNotEmpty()) {
                    Room::whereIn('room_id', $roomIds)->update(['status' => 'available']);
                }
                
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Tenant bookings deleted successfully',
                    'updated_room_ids' => $roomIds
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error deleting tenant bookings: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete tenant bookings: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getLandlordBoardingHouses()
    {
        try {
            $landlordBoardingHouses = BoardingHouse::where('is_admin', false)
                ->with('landlord')  // If you want to include landlord information
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $landlordBoardingHouses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch landlord boarding houses: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchPayments()
    {
        try {
            Log::info('Starting fetchPayments...');
            
            $payments = Payment::with([
                'booking.user:user_id,name',
                'booking.room.boardingHouse:boarding_house_id,name,description,address',
                'booking:booking_id,user_id,room_id,check_in_date,check_out_date',
                'booking.payments' // Include all payments for the booking
            ])->orderBy('created_at', 'desc')
            ->get();

            $mappedPayments = $payments->map(function($payment) {
                if (!$payment->booking || !$payment->booking->room || !$payment->booking->room->boardingHouse) {
                    Log::warning('Invalid payment record found:', [
                        'payment_id' => $payment->payment_id,
                        'amount' => $payment->amount
                    ]);
                    return null;
                }

                $boardingHouse = $payment->booking->room->boardingHouse;
                // Calculate total paid amount for this booking
                $totalPaid = $payment->booking->payments
                    ->whereIn('status', ['completed', 'confirmed'])
                    ->sum('amount');

                // Determine payment status
                $paymentStatus = 'unpaid';
                $roomPrice = floatval($payment->booking->room->price);
                if ($totalPaid >= $roomPrice) {
                    $paymentStatus = 'completed';
                } elseif ($totalPaid > 0) {
                    $paymentStatus = 'partially_paid';
                }

                return [
                    'payment_id' => $payment->payment_id,
                    'booking_id' => $payment->booking_id,
                    'amount' => floatval($payment->amount),
                    'status' => $payment->status,
                    'payment_status' => $paymentStatus,
                    'total_paid' => $totalPaid,
                    'payment_method' => $payment->payment_method,
                    'payment_proof' => $payment->payment_proof ? 
                        Storage::disk('public')->exists($payment->payment_proof) ? 
                            asset('storage/' . $payment->payment_proof) : 
                            null : null,
                    'reference_number' => $payment->reference_number ?? 'N/A',
                    'created_at' => $payment->created_at,
                    'booking' => [
                        'id' => $payment->booking->booking_id,
                        'check_in_date' => $payment->booking->check_in_date,
                        'check_out_date' => $payment->booking->check_out_date,
                        'user' => [
                            'name' => $payment->booking->user->name
                        ],
                        'room' => [
                            'room_name' => $payment->booking->room->room_name,
                            'price' => $roomPrice,
                            'boarding_house' => [
                                'id' => $boardingHouse->boarding_house_id,
                                'name' => $boardingHouse->name,
                                'description' => $boardingHouse->description,
                                'address' => $boardingHouse->address
                            ]
                        ]
                    ]
                ];
            })
            ->filter()
            ->values();

            // Calculate payment stats
            $stats = [
                'pending' => $payments->where('status', 'pending')->count(),
                'completed' => $payments->whereIn('status', ['completed', 'confirmed'])->count(),
                'failed' => $payments->where('status', 'failed')->count()
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'payments' => $mappedPayments,
                    'stats' => $stats,
                    'debug' => [
                        'total_amount' => $mappedPayments->sum('amount'),
                        'count' => $mappedPayments->count()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching payments: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch payments'
            ], 500);
        }
    }

    public function updatePaymentStatus(Request $request, $paymentId)
    {
        try {
            $payment = Payment::findOrFail($paymentId);
            
            $request->validate([
                'status' => 'required|in:pending,completed,failed'
            ]);

            $payment->status = $request->status;
            $payment->save();

            // Get the booking and calculate payment status
            $booking = $payment->booking;
            if ($booking) {
                $totalPaid = $booking->payments()
                    ->whereIn('status', ['completed', 'confirmed'])
                    ->sum('amount');
                
                $paymentStatus = '';
                if ($totalPaid >= $booking->room->price) {
                    $paymentStatus = 'completed';
                } elseif ($totalPaid > 0) {
                    $paymentStatus = 'partially_paid';
                } else {
                    $paymentStatus = 'unpaid';
                }

                // Create notification for the user
                $notificationMessage = '';
                switch ($request->status) {
                    case 'completed':
                        $notificationMessage = "Your payment of ₱{$payment->amount} for booking #{$booking->booking_id} has been confirmed. Status: " . ucfirst($paymentStatus);
                        break;
                    case 'failed':
                        $notificationMessage = "Your payment of ₱{$payment->amount} for booking #{$booking->booking_id} has been rejected.";
                        break;
                }

                if ($notificationMessage) {
                    Notification::create([
                        'user_id' => $booking->user_id,
                        'type' => 'payment',
                        'message' => $notificationMessage,
                        'link' => "/user/view-booking/{$booking->booking_id}",
                        'read' => false
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Payment status updated successfully',
                'data' => [
                    'payment' => $payment,
                    'payment_status' => $paymentStatus ?? null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating payment status: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRecentActivities()
    {
        try {
            Log::info('Starting getRecentActivities...');
            
            $activities = Activity::with(['performer', 'boardingHouse'])
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

    // Add this new method to handle landlord payment status updates
    public function updateLandlordPaymentStatus(Request $request, $paymentId)
    {
        try {
            $payment = Payment::findOrFail($paymentId);
            
            // Validate request
            $request->validate([
                'status' => 'required|in:pending,completed,failed'
            ]);

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

            // Get the booking and calculate payment status
            $booking = $payment->booking;
            if ($booking) {
                $totalPaid = $booking->payments()
                    ->whereIn('status', ['completed', 'confirmed'])
                    ->sum('amount');
                
                $paymentStatus = '';
                if ($totalPaid >= $booking->room->price) {
                    $paymentStatus = 'completed';
                } elseif ($totalPaid > 0) {
                    $paymentStatus = 'partially_paid';
                } else {
                    $paymentStatus = 'unpaid';
                }

                // Create notification for the user
                $notificationMessage = '';
                switch ($request->status) {
                    case 'completed':
                        $notificationMessage = "Your payment of ₱{$payment->amount} for booking #{$booking->booking_id} has been confirmed. Status: " . ucfirst($paymentStatus);
                        break;
                    case 'failed':
                        $notificationMessage = "Your payment of ₱{$payment->amount} for booking #{$booking->booking_id} has been rejected.";
                        break;
                }

                if ($notificationMessage) {
                    Notification::create([
                        'user_id' => $booking->user_id,
                        'type' => 'payment',
                        'message' => $notificationMessage,
                        'link' => "/user/view-booking/{$booking->booking_id}",
                        'read' => false
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Payment status updated successfully',
                'data' => [
                    'payment' => $payment,
                    'payment_status' => $paymentStatus ?? null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating payment status: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    // Add this method in the AdminController class
    public function getReviews()
    {
        try {
            $reviews = \App\Models\Review::select(
                'reviews.*',
                'users.name as user_name',
                'users.profile_picture as user_profile',
                'users.user_id',
                'rooms.room_name',
                'boarding_houses.name as boarding_house_name'
            )
            ->join('users', 'reviews.user_id', '=', 'users.user_id')
            ->join('rooms', 'reviews.room_id', '=', 'rooms.room_id')
            ->join('boarding_houses', 'reviews.boarding_house_id', '=', 'boarding_houses.boarding_house_id')
            ->orderBy('reviews.created_at', 'desc')
            ->get()
            ->map(function ($review) {
                // Format profile picture URL
                $profilePicture = null;
                if ($review->user_profile) {
                    $profilePath = str_replace('public/', '', $review->user_profile);
                    if (\Storage::disk('public')->exists($profilePath)) {
                        $profilePicture = asset('storage/' . $profilePath);
                    }
                }

                // Format review images
                $images = [];
                if ($review->images) {
                    $imageArray = json_decode($review->images, true);
                    if (is_array($imageArray)) {
                        $images = array_map(function($image) {
                            return asset('storage/' . $image);
                        }, $imageArray);
                    }
                }

                return [
                    'id' => $review->review_id,
                    'user_id' => $review->user_id,
                    'user_name' => $review->user_name,
                    'user_profile' => $profilePicture,
                    'room_name' => $review->room_name,
                    'boarding_house_name' => $review->boarding_house_name,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'images' => $images,
                    'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                    'formatted_date' => $review->created_at->diffForHumans()
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $reviews
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching reviews: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch reviews: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteReview($id)
    {
        try {
            $review = \App\Models\Review::findOrFail($id);
            
            // Delete review images if they exist
            if ($review->images) {
                $images = json_decode($review->images, true);
                if (is_array($images)) {
                    foreach ($images as $image) {
                        \Storage::disk('public')->delete($image);
                    }
                }
            }
            
            // Delete the review
            $review->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Review deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting review: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete review: ' . $e->getMessage()
            ], 500);
        }
    }

}