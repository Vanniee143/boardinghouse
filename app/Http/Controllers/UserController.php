<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    // Registration method (with profile picture and CSRF protection)
    public function register(Request $request)
    {
        $rules = [
            'fullName' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            // Handle profile picture upload
            $profilePicturePath = null;
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $filename = time() . '_' . $file->getClientOriginalName();
                // Store with explicit path using forward slashes
                $profilePicturePath = $file->storeAs(
                    'profile-pictures',
                    $filename,
                    'public'
                );
                
                // Log the path for debugging
                \Log::info('Profile picture stored at: ' . $profilePicturePath);
            }

            // Create user with profile picture
            $user = User::create([
                'name' => $request->fullName,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'profile_picture' => $profilePicturePath,
            ]);

            return response()->json([
                'message' => 'Registration successful!',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method to fetch the authenticated user's profile (with profile picture)
    public function getProfile(Request $request)
    {
        try {
            // Get authenticated user
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Not authenticated'
                ], 401);
            }

            // Handle profile picture URL
            $profilePictureUrl = null;
            if ($user->profile_picture) {
                if (Storage::disk('public')->exists($user->profile_picture)) {
                    $profilePictureUrl = Storage::url($user->profile_picture);
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'user_id' => $user->user_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_picture' => $profilePictureUrl,
                    'userType' => $user->userType,
                    'phone_number' => $user->phone_number ?? '',
                    'gender' => $user->gender ?? '',
                    'birthdate' => $user->birthdate ?? ''
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Profile fetch error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch profile'
            ], 500);
        }
    }

    // Update profile picture method
    public function updateProfilePicture(Request $request)
    {
        try {
            $request->validate([
                'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $user = auth()->user();

            // Delete old profile picture if exists
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Store new profile picture
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            
            // Update user's profile picture path
            $user->profile_picture = $path;
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Profile picture updated successfully',
                'path' => asset('storage/' . $path)
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating profile picture: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update profile picture: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchProfile($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Add profile picture URL if exists
            if ($user->profile_picture) {
                // Ensure the profile picture path is properly formatted for URL
                $user->profile_picture = asset('storage/' . $user->profile_picture);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'gender' => $user->gender,
                    'birthdate' => $user->birthdate,
                    'profile_picture' => $user->profile_picture,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching user profile: ' . $e->getMessage());
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
            
            if ($user->userType !== 'user') {
                throw new \Exception('Unauthorized access');
            }

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id . ',user_id',
                'phone_number' => 'nullable|string|max:20',
                'gender' => 'nullable|string|in:Male,Female,Other',
                'birthdate' => 'nullable|date'
            ]);

            $user->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating user profile: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    public function changePassword(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            
            if ($user->userType !== 'user') {
                throw new \Exception('Unauthorized access');
            }

            $validatedData = $request->validate([
                'old_password' => 'required|string',
                'new_password' => 'required|string|min:8'
            ]);

            if (!Hash::check($validatedData['old_password'], $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Current password is incorrect'
                ], 400);
            }

            $user->password = Hash::make($validatedData['new_password']);
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Password changed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error changing password: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to change password: ' . $e->getMessage()
            ], 500);
        }
    }

    // Add a new method to handle QR code image URLs
    public function getQRImageUrl($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::url($path);
        }
        return null;
    }
}
