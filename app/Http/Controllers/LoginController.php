<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            // Validate request
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
                'userType' => 'required|in:admin,landlord,user'
            ]);

            // Check if user exists and has correct userType
            $user = User::where('email', $credentials['email'])
                        ->where('userType', $credentials['userType'])
                        ->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Important: Actually log the user in
            Auth::login($user);
            
            // Regenerate session
            $request->session()->regenerate();

            // Store user data in session
            session([
                'user_id' => $user->user_id,
                'userType' => $user->userType,
                'userName' => $user->name
            ]);

            // Handle profile picture URL
            $profilePictureUrl = null;
            if ($user->profile_picture) {
                if (Storage::disk('public')->exists($user->profile_picture)) {
                    $profilePictureUrl = asset('storage/' . $user->profile_picture);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'userId' => $user->user_id,
                    'userType' => $user->userType,
                    'userName' => $user->name,
                    'email' => $user->email,
                    'profile_picture' => $profilePictureUrl,
                    'phone_number' => $user->phone_number,
                    'gender' => $user->gender,
                    'birthdate' => $user->birthdate
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred during login: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Clear authentication
            Auth::logout();
            
            // Invalidate and regenerate session
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
}
