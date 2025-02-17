<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Verify CSRF token is present
            if (!$request->hasHeader('X-CSRF-TOKEN')) {
                Log::error('CSRF token missing in booking request');
                return response()->json([
                    'status' => 'error',
                    'message' => 'CSRF token missing'
                ], 419);
            }

            // Validate booking data
            $validated = $request->validate([
                'user_id' => 'required|exists:users,user_id',
                'room_id' => 'required|exists:rooms,room_id',
                'check_in_date' => 'required|date',
                'check_out_date' => 'required|date|after:check_in_date',
                'status' => 'required|in:pending,confirmed,cancelled'
            ]);

            $booking = Booking::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Booking created successfully',
                'booking_id' => $booking->booking_id
            ]);

        } catch (\Exception $e) {
            Log::error('Booking creation failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($booking_id)
    {
        try {
            $booking = Booking::with(['room.boardingHouse'])
                ->where('booking_id', $booking_id)
                ->firstOrFail();
            
            // Format response to match frontend expectations
            $response = [
                'id' => $booking->booking_id,
                'room_name' => $booking->room->room_name,
                'price' => $booking->room->price,
                'bed_quantity' => $booking->room->bed_quantity,
                'status' => $booking->status,
                'room_images' => $booking->room->room_images,
                'description' => $booking->room->description,
                'map_link' => $booking->room->map_link,
                'check_in_date' => $booking->check_in_date,
                'check_out_date' => $booking->check_out_date,
                'boarding_house' => [
                    'name' => $booking->room->boardingHouse->name,
                    'address' => $booking->room->boardingHouse->address,
                    'landlord_phone' => $booking->room->boardingHouse->landlord_phone,
                    'description' => $booking->room->boardingHouse->description
                ]
            ];

            return response()->json([
                'status' => 'success',
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching booking: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch booking details'
            ], 500);
        }
    }

    public function cancel($booking_id)
    {
        try {
            $booking = Booking::where('booking_id', $booking_id)->firstOrFail();
            $booking->status = 'cancelled';
            $booking->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Booking cancelled successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error cancelling booking: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel booking'
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $userId = $request->header('X-User-Id');
            
            $bookings = Booking::with(['room.boardingHouse', 'payments' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($booking) {
                $totalPrice = $booking->room->price;
                $totalPaid = $booking->payments()
                    ->where('status', 'completed')
                    ->sum('amount');
                    
                return [
                    'booking_id' => $booking->booking_id,
                    'check_in_date' => $booking->check_in_date,
                    'check_out_date' => $booking->check_out_date,
                    'status' => $booking->status,
                    'created_at' => $booking->created_at,
                    'room' => $booking->room,
                    'payments' => $booking->payments,
                    'payment_status' => $totalPaid >= $totalPrice ? 'completed' : 
                                      ($totalPaid > 0 ? 'partially_paid' : 'unpaid'),
                    'total_paid' => $totalPaid
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
                'message' => 'Failed to fetch bookings'
            ], 500);
        }
    }

    public function destroy($booking_id)
    {
        try {
            $booking = Booking::where('booking_id', $booking_id)
                ->where('status', 'cancelled')
                ->firstOrFail();
            
            $booking->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Booking deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting booking: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete booking'
            ], 500);
        }
    }

    public function confirmBooking($bookingId)
    {
        try {
            DB::beginTransaction();

            $booking = Booking::with('room')->findOrFail($bookingId);
            $booking->status = 'confirmed';
            $booking->save();

            // Update room status to occupied
            if ($booking->room) {
                $booking->room->status = 'occupied';
                $booking->room->save();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Booking confirmed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error confirming booking: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to confirm booking'
            ], 500);
        }
    }

    public function cancelBooking($bookingId)
    {
        try {
            DB::beginTransaction();

            $booking = Booking::with('room')->findOrFail($bookingId);
            $booking->status = 'cancelled';
            $booking->save();

            // Update room status back to available
            if ($booking->room && $booking->room->status === 'occupied') {
                $booking->room->status = 'available';
                $booking->room->save();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Booking cancelled successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling booking: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel booking'
            ], 500);
        }
    }

    public function getBookings()
    {
        try {
            $bookings = Booking::with(['user', 'room', 'payments', 'boardingHouse'])
                ->get()
                ->map(function ($booking) {
                    // Debug log
                    \Log::info('Booking ' . $booking->booking_id . ' details:', [
                        'booking_id' => $booking->booking_id,
                        'room_price' => $booking->room->price,
                        'payments' => $booking->payments->map(function($payment) {
                            return [
                                'payment_id' => $payment->payment_id,
                                'amount' => floatval($payment->amount),
                                'status' => $payment->status
                            ];
                        })->toArray()
                    ]);

                    return [
                        'id' => $booking->booking_id,
                        'booking_id' => $booking->booking_id,
                        'tenant_name' => $booking->user->name,
                        'tenant_email' => $booking->user->email,
                        'tenant_phone' => $booking->user->phone_number,
                        'tenant_gender' => $booking->user->gender,
                        'room_name' => $booking->room->room_name,
                        'room' => [
                            'price' => floatval($booking->room->price),
                            'room_id' => $booking->room->room_id
                        ],
                        'boarding_house_name' => $booking->boardingHouse->name,
                        'check_in_date' => $booking->check_in_date,
                        'check_out_date' => $booking->check_out_date,
                        'status' => $booking->status,
                        'payments' => $booking->payments->map(function ($payment) {
                            return [
                                'payment_id' => $payment->payment_id,
                                'amount' => floatval($payment->amount),
                                'status' => $payment->status,
                                'created_at' => $payment->created_at
                            ];
                        })->toArray() // Make sure to convert to array
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
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 