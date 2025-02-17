<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        try {
            Log::info('Payment creation request received:', $request->all());

            // Validate the request
            $validatedData = $request->validate([
                'booking_id' => 'required|exists:bookings,booking_id',
                'amount' => 'required|numeric|min:0',
                'payment_method' => 'required|in:cash,gcash_qr,paymaya_qr',
                'payment_proof' => 'required_unless:payment_method,cash|image|mimes:jpeg,png,jpg|max:2048',
                'reference_number' => 'required_unless:payment_method,cash|string'
            ]);

            // Get the booking
            $booking = Booking::with('room')->findOrFail($validatedData['booking_id']);

            // Handle payment proof upload if provided
            $paymentProofPath = null;
            if ($request->hasFile('payment_proof')) {
                $paymentProofPath = $request->file('payment_proof')->store('payment-proofs', 'public');
            }

            // Create the payment record
            $payment = Payment::create([
                'booking_id' => $validatedData['booking_id'],
                'amount' => $validatedData['amount'],
                'payment_method' => $validatedData['payment_method'],
                'payment_proof' => $paymentProofPath,
                'reference_number' => $validatedData['reference_number'] ?? null,
                'status' => 'pending' // Initial status
            ]);

            // Create notification for admin
            Notification::create([
                'user_id' => 1, // Assuming admin has user_id 1, adjust as needed
                'type' => 'payment',
                'message' => "New payment received for Booking #{$booking->booking_id}. Amount: ₱{$validatedData['amount']}",
                'link' => "/admin/payments",
                'read' => false
            ]);

            Log::info('Payment created successfully:', ['payment_id' => $payment->payment_id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment submitted successfully',
                'data' => $payment
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating payment: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $payment = Payment::findOrFail($id);
            
            $validatedData = $request->validate([
                'status' => 'required|in:pending,completed,failed'
            ]);

            $payment->status = $validatedData['status'];
            $payment->save();

            // Generate receipt if payment is completed
            if ($validatedData['status'] === 'completed') {
                app(ReceiptController::class)->generateReceipt($payment->payment_id);
            }

            // Create notification for the user
            if ($payment->booking) {
                $message = '';
                switch ($validatedData['status']) {
                    case 'completed':
                        $message = "Your payment of ₱{$payment->amount} has been confirmed. Check your notifications for the receipt.";
                        break;
                    case 'failed':
                        $message = "Your payment of ₱{$payment->amount} was declined.";
                        break;
                }

                if ($message) {
                    Notification::create([
                        'user_id' => $payment->booking->user_id,
                        'type' => 'payment',
                        'message' => $message,
                        'link' => "/user/view-booking/{$payment->booking->booking_id}",
                        'read' => false
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Payment status updated successfully',
                'data' => $payment
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating payment: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPaymentStatus($bookingId)
    {
        try {
            $booking = Booking::with(['payments', 'room'])->findOrFail($bookingId);
            
            $totalPrice = $booking->room->price;
            $totalPaid = $booking->payments()
                ->whereIn('status', ['completed', 'confirmed'])
                ->sum('amount');
            
            $status = 'unpaid';
            if ($totalPaid >= $totalPrice) {
                $status = 'completed';
            } elseif ($totalPaid > 0) {
                $status = 'partially_paid';
            }
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'payment_status' => $status,
                    'total_paid' => $totalPaid,
                    'total_price' => $totalPrice,
                    'remaining_amount' => $totalPrice - $totalPaid
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch payment status'
            ], 500);
        }
    }

    public function updatePaymentStatus($paymentId, Request $request)
    {
        try {
            $payment = Payment::findOrFail($paymentId);
            $payment->status = $request->status;
            $payment->save();

            // Recalculate booking payment status
            $booking = $payment->booking;
            $totalPaid = $booking->payments()
                ->whereIn('status', ['completed', 'confirmed'])
                ->sum('amount');
            
            // Update booking payment_status if needed
            if ($totalPaid >= $booking->room->price) {
                $booking->payment_status = 'completed';
            } elseif ($totalPaid > 0) {
                $booking->payment_status = 'partially_paid';
            } else {
                $booking->payment_status = 'unpaid';
            }
            $booking->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment status updated successfully',
                'data' => [
                    'payment' => $payment,
                    'booking_status' => $booking->payment_status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update payment status'
            ], 500);
        }
    }
} 